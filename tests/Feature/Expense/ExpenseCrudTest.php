<?php

use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use Inertia\Testing\AssertableInertia as Assert;

it('creates an expense for an owned budget', function () {
    $user = createVerifiedUser();
    $budget = Budget::factory()->for($user)->create(['amount' => 500]);

    $this->actingAs($user)
        ->post(route('budgets.expenses.store', $budget), [
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

    $expense = Expense::where('budget_id', $budget->id)->firstOrFail();
    assert($expense instanceof Expense);
    expect($expense->name)->toBe('Supermarket Groceries')
        ->and($expense->amount)->toBe('85.50')
        ->and($expense->category)->toBe(ExpenseCategory::Food);
});

it('updates an owned expense', function () {
    $user = createVerifiedUser();
    $budget = Budget::factory()->for($user)->create(['amount' => 500]);
    $expense = Expense::factory()->for($budget)->create([
        'name' => 'Old Name',
        'amount' => 10,
    ]);

    $this->actingAs($user)
        ->put(route('expenses.update', $expense), [
            'name' => 'Updated Expense Name',
            'amount' => 25.99,
            'category' => 'entertainment',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_updated'));

    $updated = $expense->fresh();
    assert($updated instanceof Expense);
    expect($updated->name)->toBe('Updated Expense Name')
        ->and($updated->amount)->toBe('25.99')
        ->and($updated->category)->toBe(ExpenseCategory::Entertainment);
});

it('soft deletes an owned expense', function () {
    $user = createVerifiedUser();
    $budget = Budget::factory()->for($user)->create();
    $expense = Expense::factory()->for($budget)->create();

    $this->actingAs($user)
        ->delete(route('expenses.destroy', $expense))
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_deleted'));

    $this->assertSoftDeleted($expense);
});

it('includes only active expenses in budget show inertia response and excludes soft deleted expenses', function () {
    $user = createVerifiedUser();
    $budget = Budget::factory()->for($user)->create();
    $activeExpense = Expense::factory()->for($budget)->create(['name' => 'Dinner Out']);
    $deletedExpense = Expense::factory()->for($budget)->create(['name' => 'Cancelled Expense']);

    $deletedExpense->delete();

    $this->actingAs($user)
        ->get(route('budgets.show', $budget))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Budgets/Show')
            ->has('budget.expenses', 1)
            ->where('budget.expenses.0.name', 'Dinner Out')
        );

    $this->assertSoftDeleted('expenses', [
        'id' => $deletedExpense->id,
    ]);

    $this->assertDatabaseHas('expenses', [
        'id' => $activeExpense->id,
        'deleted_at' => null,
    ]);
});

it('shares flash status and status_type to inertia props upon creating an expense', function () {
    $user = createVerifiedUser();
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

it('allows an admin to create update and delete an expense on any budget', function () {
    $admin = createAdminUser();
    $otherUser = createVerifiedUser();
    $budget = Budget::factory()->for($otherUser)->create(['amount' => 500]);

    // Admin creates expense
    $this->actingAs($admin)
        ->post(route('budgets.expenses.store', $budget), [
            'name' => 'Admin Expense',
            'amount' => 50.00,
            'category' => 'food',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_created'));

    $expense = Expense::query()->where('name', 'Admin Expense')->first();
    expect($expense)->not->toBeNull();

    // Admin updates expense
    $this->actingAs($admin)
        ->put(route('expenses.update', $expense), [
            'name' => 'Admin Expense Updated',
            'amount' => 75.00,
            'category' => 'other',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_updated'));

    $updated = $expense->fresh();
    assert($updated instanceof Expense);
    expect($updated->name)->toBe('Admin Expense Updated');

    // Admin deletes expense
    $this->actingAs($admin)
        ->delete(route('expenses.destroy', $expense))
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_deleted'));

    $this->assertSoftDeleted($expense);
});

it('displays translated flash status messages when creating updating and deleting an expense in Spanish and English', function () {
    $user = createVerifiedUser();
    $budget = Budget::factory()->for($user)->create(['amount' => 500]);

    // Spanish locale
    app()->setLocale('es');
    $this->actingAs($user)
        ->withSession(['locale' => 'es'])
        ->post(route('budgets.expenses.store', $budget), [
            'name' => 'Gasto de Prueba',
            'amount' => 20.00,
            'category' => 'food',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_created'));

    $expense = Expense::query()->where('name', 'Gasto de Prueba')->first();

    $this->actingAs($user)
        ->withSession(['locale' => 'es'])
        ->put(route('expenses.update', $expense), [
            'name' => 'Gasto Editado',
            'amount' => 30.00,
            'category' => 'food',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_updated'));

    $this->actingAs($user)
        ->withSession(['locale' => 'es'])
        ->delete(route('expenses.destroy', $expense))
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_deleted'));

    // English locale
    app()->setLocale('en');
    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->post(route('budgets.expenses.store', $budget), [
            'name' => 'English Test Expense',
            'amount' => 15.00,
            'category' => 'food',
        ])
        ->assertRedirect()
        ->assertSessionHas('status', __('messages.expense_created'));
});
