<?php

use App\Enums\BudgetType;
use App\Enums\Currency;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;

it('belongs to a user', function () {
    $user = User::factory()->create();
    $budget = Budget::factory()->for($user)->create();

    expect($budget->user)->toBeInstanceOf(User::class)
        ->and($budget->user->is($user))->toBeTrue();
});

it('has many expenses', function () {
    $budget = Budget::factory()->create();
    Expense::factory()->count(3)->for($budget)->create();

    expect($budget->expenses)->toHaveCount(3)
        ->and($budget->expenses->first())->toBeInstanceOf(Expense::class);
});

it('casts amount to two decimal places', function () {
    $budget = Budget::factory()->create([
        'amount' => 99.999,
    ]);

    expect($budget->amount)->toBe('100.00');
});

it('casts type to BudgetType enum', function () {
    $budget = Budget::factory()->create([
        'type' => 'goal',
    ]);

    expect($budget->type)->toBe(BudgetType::Goal);
});

it('allows nullable description', function () {
    $budget = Budget::factory()->create([
        'description' => null,
    ]);

    expect($budget->description)->toBeNull();
});

it('formats amount correctly according to user currency and app locale', function () {
    $eurUser = User::factory()->create(['currency' => Currency::EUR]);
    $usdUser = User::factory()->create(['currency' => Currency::USD]);

    $budgetEur = Budget::factory()->for($eurUser)->create(['amount' => 1234.56]);
    $budgetUsd = Budget::factory()->for($usdUser)->create(['amount' => 1234.56]);

    // Spanish locale
    app()->setLocale('es');
    expect($budgetEur->formattedAmount())->toBe('1.234,56 €')
        ->and($budgetUsd->formattedAmount())->toBe('1.234,56 $');

    // English locale
    app()->setLocale('en');
    expect($budgetEur->formattedAmount())->toBe('1,234.56 €')
        ->and($budgetUsd->formattedAmount())->toBe('1,234.56 $');
});

it('supports soft deletes', function () {
    $budget = Budget::factory()->create();

    $budget->delete();

    $this->assertSoftDeleted($budget);

    $budget->restore();

    expect($budget->fresh()->deleted_at)->toBeNull();
});
