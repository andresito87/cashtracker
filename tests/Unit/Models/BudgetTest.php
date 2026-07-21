<?php

use App\Models\Budget;
use App\Models\User;

it('belongs to a user', function () {
    $user = User::factory()->create();
    $budget = Budget::factory()->for($user)->create();

    expect($budget->user)->toBeInstanceOf(User::class)
        ->and($budget->user->is($user))->toBeTrue();
});

it('casts amount to two decimal places', function () {
    $budget = Budget::factory()->create([
        'amount' => 99.999,
    ]);

    expect($budget->amount)->toBe('100.00');
});

it('allows nullable description', function () {
    $budget = Budget::factory()->create([
        'description' => null,
    ]);

    expect($budget->description)->toBeNull();
});
