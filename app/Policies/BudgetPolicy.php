<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
        return $user->id === $budget->user_id
            ? Response::allow()
            : Response::deny(__('messages.error_403_subtitle'));
    }

    /**
     * Determine whether the user can create a budget.
     * Any authenticated user can create their own budgets.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update a budget.
     */
    public function update(User $user, Budget $budget): Response
    {
        return $user->id === $budget->user_id
            ? Response::allow()
            : Response::deny(__('messages.error_403_subtitle'));
    }

    /**
     * Determine whether the user can delete a budget.
     */
    public function delete(User $user, Budget $budget): Response
    {
        return $user->id === $budget->user_id
            ? Response::allow()
            : Response::deny(__('messages.error_403_subtitle'));
    }
}
