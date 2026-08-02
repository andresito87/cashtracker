<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature', 'Unit');

/**
 * Custom expectation to assert that a given notification was sent to a notifiable entity.
 */
expect()->extend('toHaveBeenNotifiedOf', function (string $notification, ?callable $callback = null) {
    Notification::assertSentTo($this->value, $notification, $callback);

    return $this;
});

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

function actingAsSubscribedUser(array $attributes = []): User
{
    $user = createVerifiedUser($attributes);

    $stripePrice = config('services.stripe.price_ai_monthly');

    if (! $stripePrice) {
        throw new RuntimeException('Missing config: services.stripe.price_ai_monthly. Set it in .env.testing or config/services.php.');
    }

    $user->subscriptions()->forceCreate([
        'type' => 'default',
        'stripe_id' => 'sub_test_'.uniqid(),
        'stripe_status' => 'active',
        'stripe_price' => $stripePrice,
    ]);

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
