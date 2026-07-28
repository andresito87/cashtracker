<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('seeds expenses for all budgets of all users', function () {
    $this->seed(DatabaseSeeder::class);

    $users = User::all();

    expect($users)->toHaveCount(3);

    foreach ($users as $user) {
        expect($user->budgets)->not->toBeEmpty()
            ->and($user->expenses)->not->toBeEmpty();

        foreach ($user->budgets as $budget) {
            expect($budget->expenses->count())->toBeGreaterThanOrEqual(3)
                ->and($budget->expenses->count())->toBeLessThanOrEqual(10)
                ->and((float) $budget->expenses->sum('amount'))->toBeLessThanOrEqual((float) $budget->amount);
        }
    }
});

it('tests user expenses hasManyThrough relationship', function () {
    $this->seed(DatabaseSeeder::class);

    $user = User::where('email', 'andres@example.com')->firstOrFail();

    expect($user->expenses)->not->toBeEmpty()
        ->and($user->expenses->first()->budget->user_id)->toBe($user->id);
});
