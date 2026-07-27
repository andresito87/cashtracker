<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    /**
     * Store a newly created expense in storage for a budget.
     */
    public function store(ExpenseRequest $request, Budget $budget): RedirectResponse
    {
        Gate::authorize('create', [Expense::class, $budget]);

        $budget->expenses()->create($request->validated());

        return redirect()
            ->back()
            ->with([
                'status' => __('messages.expense_created'),
                'status_type' => 'success',
            ]);
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(ExpenseRequest $request, Expense $expense): RedirectResponse
    {
        Gate::authorize('update', $expense);

        $expense->update($request->validated());

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

        $expense->delete();

        return redirect()
            ->back()
            ->with([
                'status' => __('messages.expense_deleted'),
                'status_type' => 'success',
            ]);
    }
}
