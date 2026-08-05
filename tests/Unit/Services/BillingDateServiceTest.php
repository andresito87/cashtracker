<?php

use App\Enums\Currency;
use App\Models\User;
use App\Services\BillingDateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;

function billingDateService(): BillingDateService
{
    return app(BillingDateService::class);
}

function makeUserWithSub(array $subAttributes = []): User
{
    $user = User::factory()->create(['currency' => Currency::EUR]);
    $user->subscriptions()->forceCreate(array_merge([
        'type' => 'default',
        'stripe_id' => 'sub_'.Str::random(10),
        'stripe_status' => 'active',
        'stripe_price' => config('services.stripe.price_ai_monthly'),
        'ends_at' => null,
    ], $subAttributes));

    return $user;
}

describe('BillingDateService::for', function () {
    it('returns the ends_at date as the next billing date for a canceled subscription', function () {
        $user = makeUserWithSub(['ends_at' => now()->addDays(10)]);

        $date = billingDateService()->for($user);

        expect($date)->not->toBeNull()
            ->and($date?->format('Y-m-d'))->toBe(now()->addDays(10)->format('Y-m-d'));
    });

    it('returns null when the user has no subscription', function () {
        $user = User::factory()->create();

        expect(billingDateService()->for($user))->toBeNull();
    });

    it('caches the computed date so a second call within TTL returns the same value without recomputation', function () {
        $user = makeUserWithSub(['ends_at' => now()->addDays(20)]);

        $first = billingDateService()->for($user);
        $second = billingDateService()->for($user);

        expect($first)->not->toBeNull()
            ->and($second)->not->toBeNull()
            ->and($first?->format('Y-m-d'))->toBe($second?->format('Y-m-d'));
    });
});

describe('BillingDateService::forget', function () {
    it('clears the cached billing date for a user', function () {
        $user = makeUserWithSub(['ends_at' => now()->addDays(15)]);

        billingDateService()->for($user);
        expect(cache()->has(billingDateService()->cacheKey($user)))->toBeTrue();

        billingDateService()->forget($user);

        expect(cache()->has(billingDateService()->cacheKey($user)))->toBeFalse();
    });

    it('clears the cached billing date via forgetForSubscription', function () {
        $user = makeUserWithSub(['ends_at' => now()->addDays(15)]);
        /** @var Subscription $subscription */
        $subscription = $user->subscription();

        billingDateService()->for($user);
        expect(cache()->has(billingDateService()->cacheKey($user)))->toBeTrue();

        billingDateService()->forgetForSubscription($subscription);

        expect(cache()->has(billingDateService()->cacheKey($user)))->toBeFalse();
    });
});

describe('BillingDateService — single cache namespace', function () {
    it('uses exactly one cache key per user under the configured namespace', function () {
        $user = makeUserWithSub(['ends_at' => now()->addDays(5)]);
        $namespace = 'billing.test_next_billing_date';
        $expectedKey = $namespace.':'.$user->id;

        config()->set('billing.cache_namespace', $namespace);

        Cache::expects('has')
            ->once()
            ->with($expectedKey)
            ->andReturnFalse();

        Cache::expects('put')
            ->once()
            ->with(
                $expectedKey,
                now()->addDays(5)->toDateTimeString(),
                Mockery::type(DateTimeInterface::class),
            );

        billingDateService()->for($user);
    });
});
