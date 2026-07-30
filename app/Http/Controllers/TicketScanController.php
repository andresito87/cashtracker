<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TicketScanner;
use App\Enums\Currency;
use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Ai\Files;

class TicketScanController extends Controller
{
    public function store(Request $request, Budget $budget)
    {
        Gate::authorize('update', $budget);

        $request->validate([
            'image' => 'required|image|max:2048', // Validate that the uploaded file is an image and not larger than 2MB
        ]);

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

    private function createExpenses(Budget $budget, string $store, string $category, array $items): array
    {
        $symbol = $budget->user?->currency?->symbol() ?? auth()->user()?->currency?->symbol() ?? '€';
        $categoryEnum = ExpenseCategory::tryFrom($category) ?? ExpenseCategory::Other;
        $catLabel = $categoryEnum->label();
        $created = [];

        foreach ($items as $item) {
            $name = $store.' - '.$item['name'];
            $amount = number_format((float) $item['amount'], 2);

            Expense::create([
                'budget_id' => $budget->id,
                'name' => $name,
                'amount' => $item['amount'],
                'category' => $category,
            ]);

            $created[] = "- $name: $symbol$amount ($catLabel)";
        }

        $total = array_sum(array_column($items, 'amount'));

        return [
            'success' => true,
            'message' => 'Se registraron '.count($created)." gastos del ticket:\n".
            	implode("\n", $created).
            	"\nTotal: $symbol$total",
        ];
    }
}
