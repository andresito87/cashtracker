<?php

use App\Models\Budget;
use App\Models\Expense;

it('forbids a regular user from creating an expense for another users budget and renders custom 403 page', function () {
    actingAsVerifiedUser();
    $otherUser = createVerifiedUser();
    $otherBudget = Budget::factory()->for($otherUser)->create();

    $response = $this->post(route('budgets.expenses.store', $otherBudget), [
        'name' => 'Unauthorized Expense',
        'amount' => 15.00,
        'category' => 'other',
    ]);

    $response->assertStatus(403)
        ->assertSee(__('messages.error_403_title'))
        ->assertSee(__('messages.error_back_dashboard'));

    $this->assertDatabaseMissing('expenses', [
        'budget_id' => $otherBudget->id,
        'name' => 'Unauthorized Expense',
    ]);
});

it('forbids a regular user from updating another users expense and renders custom 403 page', function () {
    actingAsVerifiedUser();
    $otherUser = createVerifiedUser();
    $otherBudget = Budget::factory()->for($otherUser)->create();
    $otherExpense = Expense::factory()->for($otherBudget)->create(['name' => 'Original Expense']);

    $response = $this->put(route('expenses.update', $otherExpense), [
        'name' => 'Hacked Expense',
        'amount' => 20.00,
        'category' => 'other',
    ]);

    $response->assertStatus(403)
        ->assertSee(__('messages.error_403_title'))
        ->assertSee(__('messages.error_back_dashboard'));

    $this->assertDatabaseMissing('expenses', [
        'id' => $otherExpense->id,
        'name' => 'Hacked Expense',
    ]);

    $updated = $otherExpense->fresh();
    assert($updated instanceof Expense);
    expect($updated->name)->toBe('Original Expense');
});

it('forbids a regular user from deleting another users expense and renders custom 403 page', function () {
    actingAsVerifiedUser();
    $otherUser = createVerifiedUser();
    $otherBudget = Budget::factory()->for($otherUser)->create();
    $otherExpense = Expense::factory()->for($otherBudget)->create();

    $response = $this->delete(route('expenses.destroy', $otherExpense));

    $response->assertStatus(403)
        ->assertSee(__('messages.error_403_title'))
        ->assertSee(__('messages.error_back_dashboard'));

    $this->assertDatabaseHas('expenses', [
        'id' => $otherExpense->id,
        'deleted_at' => null,
    ]);
});

it('allows an admin user to create update and delete expenses for any users budget', function () {
    $admin = createAdminUser();
    $otherUser = createVerifiedUser();
    $otherBudget = Budget::factory()->for($otherUser)->create(['amount' => 500]);
    $this->actingAs($admin);

    // Create
    $this->post(route('budgets.expenses.store', $otherBudget), [
        'name' => 'Admin Created Expense',
        'amount' => 50.00,
        'category' => 'food',
    ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_created'));

    $expense = Expense::query()->where('name', 'Admin Created Expense')->first();
    expect($expense)->not->toBeNull();

    // Update
    $this->put(route('expenses.update', $expense), [
        'name' => 'Admin Updated Expense',
        'amount' => 75.00,
        'category' => 'health',
    ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_updated'));

    $updated = $expense->fresh();
    assert($updated instanceof Expense);
    expect($updated->name)->toBe('Admin Updated Expense');

    // Delete
    $this->delete(route('expenses.destroy', $expense))
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_deleted'));

    $this->assertSoftDeleted($expense);
});

it('redirects authenticated but unverified users away from expense routes', function () {
    $budget = Budget::factory()->create();
    $expense = Expense::factory()->for($budget)->create();

    actingAsUnverifiedUser();

    $this->post(route('budgets.expenses.store', $budget), [
        'name' => 'Unverified Expense',
        'amount' => 10,
        'category' => 'other',
    ])->assertRedirect(route('verification.notice'));

    $this->put(route('expenses.update', $expense), [
        'name' => 'Unverified Update',
        'amount' => 15,
        'category' => 'other',
    ])->assertRedirect(route('verification.notice'));

    $this->delete(route('expenses.destroy', $expense))
        ->assertRedirect(route('verification.notice'));
});

it('returns 404 when attempting to create an expense for a soft deleted budget', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();
    $budget->delete();

    $this->post(route('budgets.expenses.store', $budget), [
        'name' => 'Expense for Deleted Budget',
        'amount' => 10,
        'category' => 'food',
    ])->assertNotFound();
});

it('returns 404 when attempting to update or delete a soft deleted expense', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();
    $expense = Expense::factory()->for($budget)->create();
    $expense->delete();

    $this->put(route('expenses.update', $expense), [
        'name' => 'Update Soft Deleted',
        'amount' => 15,
        'category' => 'food',
    ])->assertNotFound();

    $this->delete(route('expenses.destroy', $expense))
        ->assertNotFound();
});

it('redirects unauthenticated users to the login page', function () {
    $budget = Budget::factory()->create();
    $expense = Expense::factory()->for($budget)->create();

    $this->post(route('budgets.expenses.store', $budget), [
        'name' => 'Guest Expense',
        'amount' => 10,
        'category' => 'food',
    ])->assertRedirect(route('login'));

    $this->put(route('expenses.update', $expense), [
        'name' => 'Guest Update',
        'amount' => 15,
        'category' => 'food',
    ])->assertRedirect(route('login'));

    $this->delete(route('expenses.destroy', $expense))
        ->assertRedirect(route('login'));
});
