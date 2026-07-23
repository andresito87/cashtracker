<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
|
| Shared helpers for authenticated test scenarios. Use these instead of
| repeating factory + actingAs setup across feature tests.
|
*/

function createVerifiedUser(array $attributes = []): User
{
    return User::factory()->create($attributes);
}

function createUnverifiedUser(array $attributes = []): User
{
    return User::factory()->unverified()->create($attributes);
}

function createAdminUser(array $attributes = []): User
{
    return User::factory()->admin()->create($attributes);
}

function actingAsVerifiedUser(array $attributes = []): User
{
    $user = createVerifiedUser($attributes);

    test()->actingAs($user);

    return $user;
}

function actingAsUnverifiedUser(array $attributes = []): User
{
    $user = createUnverifiedUser($attributes);

    test()->actingAs($user);

    return $user;
}

function validRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'currency' => 'EUR',
        'password' => 'SecureP@ssw0rd2026!XyZ',
        'password_confirmation' => 'SecureP@ssw0rd2026!XyZ',
    ], $overrides);
}

function validBudgetPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Monthly Groceries',
        'amount' => 350.50,
        'type' => 'general',
        'description' => 'Food and household items',
    ], $overrides);
}
