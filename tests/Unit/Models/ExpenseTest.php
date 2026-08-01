<?php

use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;

it('belongs to a budget', function () {
    $budget = Budget::factory()->create();
    $expense = Expense::factory()->for($budget)->create();

    expect($expense->budget)->toBeInstanceOf(Budget::class)
        ->and($expense->budget->is($budget))->toBeTrue();
});

it('casts amount to two decimal places', function () {
    $expense = Expense::factory()->create([
        'amount' => 45.678,
    ]);

    expect($expense->amount)->toBe('45.68');
});

it('supports soft deletes', function () {
    $expense = Expense::factory()->create();

    $expense->delete();

    $this->assertSoftDeleted($expense);

    $expense->restore();

    expect($expense->fresh()->deleted_at)->toBeNull();
});

it('returns the human-readable label for a known category', function () {
    $expense = Expense::factory()->create(['category' => ExpenseCategory::Food]);

    expect($expense->categoryLabel())->toBe(ExpenseCategory::Food->label());
});
