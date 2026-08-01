<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Policy governing authorization for Budget domain models.
 *
 * Architecture Note: Domain authorization rules are centralized in Policy classes and explicitly invoked
 * via Gate::authorize() inside Controllers. This keeps HTTP FormRequests decoupled from route parameters
 * and database models, maintaining a clean Single Responsibility Principle (SRP) separation between
 * input validation (FormRequests) and domain security (Policies).
 */
class BudgetPolicy
{
    /**
     * Determine whether the user can view any budgets.
     * Only admins see the full list (admin panel). Regular users see only their own.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view a specific budget.
     */
    public function view(User $user, Budget $budget): Response
    {
        return $user->id === $budget->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny(__('messages.error_403_subtitle'));
    }

    /**
     * Determine whether the user can create a budget.
     * Any authenticated user can create their own budgets.
     */
    /** @noinspection PhpUnusedParameterInspection */
    public function create(User $_user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update a budget.
     */
    public function update(User $user, Budget $budget): Response
    {
        return $user->id === $budget->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny(__('messages.error_403_subtitle'));
    }

    /**
     * Determine whether the user can delete a budget.
     */
    public function delete(User $user, Budget $budget): Response
    {
        return $user->id === $budget->user_id || $user->isAdmin()
            ? Response::allow()
            : Response::deny(__('messages.error_403_subtitle'));
    }
}
