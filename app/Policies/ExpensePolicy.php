<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy governing authorization for Expense domain models.
 *
 * Architecture Note: Domain authorization rules are centralized in Policy classes and explicitly invoked
 * via Gate::authorize() inside Controllers. This keeps HTTP FormRequests decoupled from route parameters
 * and database models, maintaining a clean Single Responsibility Principle (SRP) separation between
 * input validation (FormRequests) and domain security (Policies).
 */
class ExpensePolicy
{
    /**
     * Determine whether the user can create an expense for the given budget.
     */
    public function create(User $user, Budget $budget): Response
    {
        return $user->id === $budget->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny(__('messages.error_403_subtitle'));
    }

    /**
     * Determine whether the user can update the expense.
     */
    public function update(User $user, Expense $expense): Response
    {
        return $user->id === $expense->budget->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny(__('messages.error_403_subtitle'));
    }

    /**
     * Determine whether the user can delete the expense.
     */
    public function delete(User $user, Expense $expense): Response
    {
        return $user->id === $expense->budget->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny(__('messages.error_403_subtitle'));
    }
}
