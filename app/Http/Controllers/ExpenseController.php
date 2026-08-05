<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Budget;
use App\Models\Expense;
use App\Services\ExpenseOverspendException;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Throwable;

class ExpenseController extends Controller
{
    /**
     * Store a newly created expense in storage for a budget.
     *
     * @throws ValidationException|Throwable
     */
    public function store(ExpenseRequest $request, Budget $budget): RedirectResponse
    {
        Gate::authorize('create', [Expense::class, $budget]);

        try {
            app(ExpenseService::class)->create($budget, $request->validated());
        } catch (ExpenseOverspendException) {
            throw ValidationException::withMessages([
                'amount' => __('messages.validation_amount_exceeds_balance'),
            ]);
        }

        return redirect()
            ->back()
            ->with([
                'status' => __('messages.expense_created'),
                'status_type' => 'success',
            ]);
    }

    /**
     * Update the specified expense in storage.
     *
     * @throws ValidationException|Throwable
     */
    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        Gate::authorize('update', $expense);

        try {
            app(ExpenseService::class)->update($expense, $request->validated());
        } catch (ExpenseOverspendException) {
            throw ValidationException::withMessages([
                'amount' => __('messages.validation_amount_exceeds_balance'),
            ]);
        }

        return redirect()
            ->back()
            ->with([
                'status' => __('messages.expense_updated'),
                'status_type' => 'success',
            ]);
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        Gate::authorize('delete', $expense);

        app(ExpenseService::class)->delete($expense);

        return redirect()
            ->back()
            ->with([
                'status' => __('messages.expense_deleted'),
                'status_type' => 'success',
            ]);
    }
}
