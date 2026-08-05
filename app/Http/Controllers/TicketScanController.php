<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TicketScanner;
use App\Enums\Currency;
use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use App\Services\ExpenseOverspendException;
use App\Services\ExpenseService;
use App\Support\MoneyAmount;
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

        $budget->loadMissing('user');
        $userCurrency = $budget->user?->currency ?? auth()->user()?->currency ?? Currency::EUR;

        $scanner = app(TicketScanner::class);
        $scanner->currency = $userCurrency;

        $response = $scanner->prompt(
            __('messages.ticket_scan_prompt', [
                'currency' => $userCurrency->value,
                'symbol' => $userCurrency->symbol(),
            ]),
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
                'message' => __('messages.ticket_scan_no_items'),
            ], status: 422);
        }

        try {
            $created = $this->createExpenses(
                $budget,
                $response['store'],
                $response['category'],
                $items
            );
        } catch (ExpenseOverspendException) {
            $symbol = $userCurrency->symbol();
            $ticketTotal = array_sum(array_map(
                fn ($item) => MoneyAmount::fromString((string) $item['amount'])->cents(),
                $items,
            )) / 100;
            $currentTotalSpent = (float) $budget->expenses()->sum('amount');
            $availableBalance = max(0, (float) $budget->amount - $currentTotalSpent);

            return response()->json([
                'success' => false,
                'message' => __('messages.ticket_scan_exceeds_balance', [
                    'symbol' => $symbol,
                    'total' => number_format($ticketTotal, 2),
                    'balance' => number_format($availableBalance, 2),
                ]),
            ], status: 422);
        }

        return response()->json($created);
    }

    /**
     * Create expenses from ticket scan results through the locked service.
     *
     * @throws ExpenseOverspendException
     */
    private function createExpenses(Budget $budget, string $store, string $category, array $items): array
    {
        $symbol = $budget->user?->currency?->symbol() ?? auth()->user()?->currency?->symbol() ?? '€';
        $categoryEnum = ExpenseCategory::tryFrom($category) ?? ExpenseCategory::Other;
        $catLabel = $categoryEnum->label();

        $rows = array_map(fn ($item) => [
            'name' => $store.' - '.$item['name'],
            'amount' => (string) $item['amount'],
            'category' => $categoryEnum,
        ], $items);

        $expenses = app(ExpenseService::class)->createMany($budget, $rows);

        $created = $expenses->map(fn (Expense $expense) => '- '.$expense->name.': '.$symbol.number_format((float) $expense->amount, 2).' ('.$catLabel.')')->all();

        // Report the sum of persisted (rounded) amounts, not the raw input.
        $total = number_format($expenses->sum(fn (Expense $expense) => (float) $expense->amount), 2);

        return [
            'success' => true,
            'message' => __('messages.ticket_scan_success', ['count' => count($created)])."\n".
                implode("\n", $created)."\n".
                __('messages.ticket_scan_total', ['symbol' => $symbol, 'total' => $total]),
        ];
    }
}
