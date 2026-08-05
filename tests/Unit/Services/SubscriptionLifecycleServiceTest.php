<?php

use App\Enums\Currency;
use App\Models\User;
use App\Services\SubscriptionConflictException;
use App\Services\SubscriptionLifecycleService;
use App\Services\SubscriptionTransitionException;
use Illuminate\Support\Str;

function lifecycleService(): SubscriptionLifecycleService
{
    return app(SubscriptionLifecycleService::class);
}

function makeUserWithActiveSub(string $priceId): User
{
    $user = User::factory()->create(['currency' => Currency::EUR]);
    $user->subscriptions()->forceCreate([
        'type' => 'default',
        'stripe_id' => 'sub_'.Str::random(10),
        'stripe_status' => 'active',
        'stripe_price' => $priceId,
    ]);

    return $user;
}

describe('SubscriptionLifecycleService — uniqueness', function () {
    it('rejects checkout when an active subscription exists', function () {
        $user = makeUserWithActiveSub(config('services.stripe.price_ai_monthly'));

        expect(fn () => lifecycleService()->assertCanCheckout($user))
            ->toThrow(SubscriptionConflictException::class);
    });

    it('allows checkout when no subscription exists', function () {
        $user = User::factory()->create();

        lifecycleService()->assertCanCheckout($user);

        expect(true)->toBeTrue();
    });
});

describe('SubscriptionLifecycleService — transitions', function () {
    it('allows monthly to yearly upgrade', function () {
        $user = makeUserWithActiveSub(config('services.stripe.price_ai_monthly'));

        lifecycleService()->assertTransition($user, 'yearly');

        expect(true)->toBeTrue();
    });

    it('forbids yearly to monthly downgrade', function () {
        $user = makeUserWithActiveSub(config('services.stripe.price_ai_yearly'));

        expect(fn () => lifecycleService()->assertTransition($user, 'monthly'))
            ->toThrow(SubscriptionTransitionException::class);
    });

    it('allows a user with no subscription to checkout any plan', function () {
        $user = User::factory()->create();

        lifecycleService()->assertTransition($user, 'monthly');
        lifecycleService()->assertTransition($user, 'yearly');

        expect(true)->toBeTrue();
    });
});

describe('SubscriptionLifecycleService — idempotent checkout', function () {
    it('stores a checkout receipt and returns it on replay with the same key', function () {
        $user = User::factory()->create();

        lifecycleService()->storeReceipt(
            $user->id,
            'monthly',
            'key-123',
            'cs_test_session_1',
            'https://checkout.stripe.com/test_1'
        );

        $receipt = lifecycleService()->findReceipt($user->id, 'key-123');

        expect($receipt)->not->toBeNull()
            ->and($receipt->stripe_session_id)->toBe('cs_test_session_1')
            ->and($receipt->stripe_url)->toBe('https://checkout.stripe.com/test_1');
    });

    it('returns null when no idempotency key is provided', function () {
        $user = User::factory()->create();

        expect(lifecycleService()->findReceipt($user->id, null))->toBeNull();
    });

    it('returns null when no prior receipt exists for the key', function () {
        $user = User::factory()->create();

        expect(lifecycleService()->findReceipt($user->id, 'no-such-key'))->toBeNull();
    });
});
