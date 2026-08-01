<?php

use App\Models\Budget;
use App\Models\Expense;

function validExpensePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Supermarket Groceries',
        'amount' => 45.50,
        'category' => 'food',
    ], $overrides);
}

it('redirects back to budget show page with session errors when store payload is empty', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->from(route('budgets.show', $budget))
        ->post(route('budgets.expenses.store', $budget), [
            'name' => '',
            'amount' => '',
            'category' => '',
        ])
        ->assertRedirect(route('budgets.show', $budget))
        ->assertSessionHasErrors(['name', 'amount', 'category']);
});

it('redirects back to budget show page with session errors when update payload is empty', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();
    $expense = Expense::factory()->for($budget)->create();

    $this->from(route('budgets.show', $budget))
        ->put(route('expenses.update', $expense), [
            'name' => '',
            'amount' => '',
            'category' => '',
        ])
        ->assertRedirect(route('budgets.show', $budget))
        ->assertSessionHasErrors(['name', 'amount', 'category']);
});

it('rejects expense name exceeding 255 characters', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->post(route('budgets.expenses.store', $budget), validExpensePayload([
        'name' => str_repeat('a', 256),
    ]))->assertSessionHasErrors(['name']);
});

it('rejects non-numeric expense amounts', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->post(route('budgets.expenses.store', $budget), validExpensePayload([
        'amount' => 'not-a-number',
    ]))->assertSessionHasErrors(['amount']);
});

it('rejects amounts with more than 2 decimal places', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->post(route('budgets.expenses.store', $budget), validExpensePayload([
        'amount' => '15.999',
    ]))->assertSessionHasErrors(['amount']);
});

it('rejects zero or negative amounts', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->post(route('budgets.expenses.store', $budget), validExpensePayload([
        'amount' => 0,
    ]))->assertSessionHasErrors(['amount']);

    $this->post(route('budgets.expenses.store', $budget), validExpensePayload([
        'amount' => -10,
    ]))->assertSessionHasErrors(['amount']);
});

it('rejects invalid category values', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->post(route('budgets.expenses.store', $budget), validExpensePayload([
        'category' => 'invalid-category',
    ]))->assertSessionHasErrors(['category']);
});

it('prevents creating an expense that exceeds the available budget balance', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create([
        'amount' => 100,
    ]);

    Expense::factory()->for($budget)->create([
        'amount' => 70,
    ]);

    $this->post(route('budgets.expenses.store', $budget), validExpensePayload([
        'name' => 'Excessive Expense',
        'amount' => 40,
    ]))->assertSessionHasErrors(['amount']);
});

it('allows creating an expense that exactly matches the remaining budget balance', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create([
        'amount' => 100,
    ]);

    Expense::factory()->for($budget)->create([
        'amount' => 70,
    ]);

    $this->post(route('budgets.expenses.store', $budget), validExpensePayload([
        'name' => 'Exact Limit Expense',
        'amount' => 30,
    ]))->assertRedirect();
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
    $this->put(route('expenses.update', $expenseB), validExpensePayload([
        'amount' => 70,
    ]))->assertSessionHasErrors(['amount']);

    // Updating Expense B to 60 should succeed.
    $this->put(route('expenses.update', $expenseB), validExpensePayload([
        'amount' => 60,
    ]))->assertRedirect();

    expect($expenseB->fresh()->amount)->toBe('60.00');
});

it('provides custom localized validation messages in Spanish and English', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    // Spanish
    app()->setLocale('es');
    $this->from(route('budgets.show', $budget))
        ->withSession(['locale' => 'es'])
        ->post(route('budgets.expenses.store', $budget))
        ->assertRedirect(route('budgets.show', $budget))
        ->assertSessionHasErrors([
            'name' => __('messages.validation_expense_name_required'),
            'amount' => __('messages.validation_amount_required'),
            'category' => __('messages.validation_category_required'),
        ]);

    // English
    app()->setLocale('en');
    $this->from(route('budgets.show', $budget))
        ->withSession(['locale' => 'en'])
        ->post(route('budgets.expenses.store', $budget))
        ->assertRedirect(route('budgets.show', $budget))
        ->assertSessionHasErrors([
            'name' => __('messages.validation_expense_name_required'),
            'amount' => __('messages.validation_amount_required'),
            'category' => __('messages.validation_category_required'),
        ]);
});
