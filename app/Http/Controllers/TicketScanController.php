<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TicketScanner;
use App\Enums\Currency;
use App\Enums\ExpenseCategory;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Laravel\Ai\Files;
use Throwable;

class TicketScanController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(Request $request, Budget $budget)
    {
        Gate::authorize('update', $budget);

        $request->validate([
            'image' => 'required|image|max:2048', // Validate that the uploaded file is an image and not larger than 2MB
        ]);

        $budget->loadMissing('user');
        $userCurrency = $budget->user?->currency ?? auth()->user()?->currency ?? Currency::EUR;

        $scanner = app(TicketScanner::class);
        $scanner->currency = $userCurrency;

        $response = $scanner->prompt(
            "Lee este ticket de venta y extrae la información estructurada en JSON. Si la moneda del ticket difiere de $userCurrency->value ({$userCurrency->symbol()}), convierte cada importe a $userCurrency->value ({$userCurrency->symbol()}).",
            attachments: [Files\Image::fromPath($request->file('image'))],
            provider: 'openrouter',
            model: 'openrouter/free',
            timeout: 120
        );

        $rawItems = $response['items'] ?? [];
        $items = array_values(array_filter(
            $rawItems,
            fn ($item) => isset($item['amount']) && (float) $item['amount'] > 0
        ));

        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron productos con un importe mayor a 0 en el ticket.',
            ], status: 422);
        }

        $ticketTotal = (float) array_sum(array_column($items, 'amount'));
        $currentTotalSpent = (float) $budget->expenses()->sum('amount');
        $availableBalance = max(0, (float) $budget->amount - $currentTotalSpent);

        if ($ticketTotal > $availableBalance) {
            $symbol = $userCurrency->symbol();
            $formattedTotal = number_format($ticketTotal, 2);
            $formattedBalance = number_format($availableBalance, 2);

            return response()->json([
                'success' => false,
                'message' => "El total del ticket ($symbol$formattedTotal) excede el saldo disponible en este presupuesto ($symbol$formattedBalance). No se registraron los gastos.",
            ], status: 422);
        }

        return response()->json(
            $this->createExpenses(
                $budget,
                $response['store'],
                $response['category'],
                $items
            )
        );
    }

    /**
     * Create expenses from ticket scan results
     *
     * @throws Throwable
     */
    private function createExpenses(Budget $budget, string $store, string $category, array $items): array
    {
        $symbol = $budget->user?->currency?->symbol() ?? auth()->user()?->currency?->symbol() ?? '€';
        $categoryEnum = ExpenseCategory::tryFrom($category) ?? ExpenseCategory::Other;
        $catLabel = $categoryEnum->label();

        $rows = array_map(fn ($item) => [
            'name' => $store.' - '.$item['name'],
            'amount' => $item['amount'],
            'category' => $categoryEnum->value,
        ], $items);

        DB::transaction(fn () => $budget->expenses()->createMany($rows));

        $created = array_map(
            fn ($row) => '- '.$row['name'].': '.$symbol.number_format((float) $row['amount'], 2).' ('.$catLabel.')',
            $rows
        );

        $total = number_format((float) array_sum(array_column($items, 'amount')), 2);

        return [
            'success' => true,
            'message' => 'Se registraron '.count($created)." gastos del ticket:\n".
                implode("\n", $created).
                "\nTotal: $symbol$total",
        ];
    }
}
