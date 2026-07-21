<?php

use App\Models\Budget;
use App\Models\User;

it('identifies admin users', function () {
    $admin = User::factory()->admin()->make();
    $user = User::factory()->make();

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isUser())->toBeFalse()
        ->and($user->isAdmin())->toBeFalse()
        ->and($user->isUser())->toBeTrue();
});

it('has many budgets', function () {
    $user = User::factory()->create();
    Budget::factory()->count(2)->for($user)->create();

    expect($user->budgets)->toHaveCount(2)
        ->and($user->budgets->first())->toBeInstanceOf(Budget::class);
});

it('hashes the password when persisting', function () {
    $user = User::factory()->create([
        'password' => 'PlainTextPassword123!',
    ]);

    expect($user->password)->not->toBe('PlainTextPassword123!');
});
