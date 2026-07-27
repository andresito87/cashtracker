<?php

use App\Models\Budget;
use App\Models\Expense;
use Inertia\Testing\AssertableInertia as Assert;

it('creates an expense for an owned budget', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->post(route('budgets.expenses.store', $budget), [
        'name' => 'Supermarket Groceries',
        'amount' => 85.50,
        'category' => 'food',
    ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_created'));

    $this->assertDatabaseHas('expenses', [
        'budget_id' => $budget->id,
        'name' => 'Supermarket Groceries',
        'amount' => 85.50,
        'category' => 'food',
    ]);
});

it('prevents creating an expense for another users budget', function () {
    actingAsVerifiedUser();
    $otherUser = createVerifiedUser();
    $budget = Budget::factory()->for($otherUser)->create();

    $this->post(route('budgets.expenses.store', $budget), [
        'name' => 'Unauthorized Expense',
        'amount' => 50,
        'category' => 'other',
    ])->assertForbidden();

    $this->assertDatabaseMissing('expenses', [
        'name' => 'Unauthorized Expense',
    ]);
});

it('validates expense creation payload', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->post(route('budgets.expenses.store', $budget), [
        'name' => '',
        'amount' => -10,
        'category' => 'invalid-category',
    ])
        ->assertSessionHasErrors(['name', 'amount', 'category']);
});

it('prevents creating an expense that exceeds the available budget balance', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create([
        'amount' => 100,
    ]);

    Expense::factory()->for($budget)->create([
        'amount' => 70,
    ]);

    $this->post(route('budgets.expenses.store', $budget), [
        'name' => 'Excessive Expense',
        'amount' => 40,
        'category' => 'other',
    ])
        ->assertSessionHasErrors(['amount']);
});

it('updates an owned expense', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();
    $expense = Expense::factory()->for($budget)->create([
        'name' => 'Old Name',
        'amount' => 10,
    ]);

    $this->put(route('expenses.update', $expense), [
        'name' => 'Updated Expense Name',
        'amount' => 25.99,
        'category' => 'entertainment',
    ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_updated'));

    expect($expense->fresh())
        ->name->toBe('Updated Expense Name')
        ->amount->toBe('25.99')
        ->category->toBe('entertainment');
});

it('prevents updating another users expense', function () {
    actingAsVerifiedUser();
    $otherUser = createVerifiedUser();
    $budget = Budget::factory()->for($otherUser)->create();
    $expense = Expense::factory()->for($budget)->create();

    $this->put(route('expenses.update', $expense), [
        'name' => 'Hacked Expense',
        'amount' => 100,
        'category' => 'other',
    ])->assertForbidden();
});

it('soft deletes an owned expense', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();
    $expense = Expense::factory()->for($budget)->create();

    $this->delete(route('expenses.destroy', $expense))
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_deleted'));

    $this->assertSoftDeleted($expense);
});

it('prevents deleting another users expense', function () {
    actingAsVerifiedUser();
    $otherUser = createVerifiedUser();
    $budget = Budget::factory()->for($otherUser)->create();
    $expense = Expense::factory()->for($budget)->create();

    $this->delete(route('expenses.destroy', $expense))->assertForbidden();

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'deleted_at' => null,
    ]);
});

it('includes expenses in budget show inertia response', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();
    Expense::factory()->for($budget)->create(['name' => 'Dinner Out']);

    $this->get(route('budgets.show', $budget))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Budgets/Show')
            ->has('budget.expenses', 1)
            ->where('budget.expenses.0.name', 'Dinner Out')
        );
});

it('shares flash status and status_type to inertia props upon creating an expense', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->actingAs($user)
        ->withSession([
            'status' => __('messages.expense_created'),
            'status_type' => 'success',
        ])
        ->get(route('budgets.show', $budget))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Budgets/Show')
            ->where('flash.status', __('messages.expense_created'))
            ->where('flash.status_type', 'success')
        );
});

it('requires authentication to create, update or delete expenses', function () {
    $budget = Budget::factory()->create();
    $expense = Expense::factory()->for($budget)->create();

    $this->post(route('budgets.expenses.store', $budget), [
        'name' => 'Guest Expense',
        'amount' => 10,
        'category' => 'other',
    ])->assertRedirect(route('login'));

    $this->put(route('expenses.update', $expense), [
        'name' => 'Guest Update',
        'amount' => 15,
        'category' => 'other',
    ])->assertRedirect(route('login'));

    $this->delete(route('expenses.destroy', $expense))
        ->assertRedirect(route('login'));
});

it('validates expense update payload', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();
    $expense = Expense::factory()->for($budget)->create();

    $this->put(route('expenses.update', $expense), [
        'name' => '',
        'amount' => 0,
        'category' => 'non-existent-category',
    ])->assertSessionHasErrors(['name', 'amount', 'category']);
});

it('prevents updating an expense to an amount exceeding the remaining budget balance', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create([
        'amount' => 100,
    ]);

    Expense::factory()->for($budget)->create(['amount' => 40]);
    $expenseB = Expense::factory()->for($budget)->create(['amount' => 30]);

    // Available balance excluding Expense B is 100 - 40 = 60.
    // Updating Expense B to 70 should fail validation.
    $this->put(route('expenses.update', $expenseB), [
        'name' => 'Expense B Updated',
        'amount' => 70,
        'category' => 'other',
    ])->assertSessionHasErrors(['amount']);

    // Updating Expense B to 60 should succeed.
    $this->put(route('expenses.update', $expenseB), [
        'name' => 'Expense B Valid',
        'amount' => 60,
        'category' => 'other',
    ])->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_updated'));

    expect($expenseB->fresh()->amount)->toBe('60.00');
});

it('validates expense name max length and amount decimal precision', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->post(route('budgets.expenses.store', $budget), [
        'name' => str_repeat('a', 256),
        'amount' => 10.1234,
        'category' => 'food',
    ])->assertSessionHasErrors(['name', 'amount']);
});
