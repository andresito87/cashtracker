<?php

use App\Enums\Currency;
use App\Models\User;
use App\Services\SubscriptionAccess;
use Illuminate\Support\Str;

function subscriptionAccess(): SubscriptionAccess
{
    return app(SubscriptionAccess::class);
}

function makeSubscribedUser(array $subAttributes = []): User
{
    $user = User::factory()->create(['currency' => Currency::EUR]);
    $stripePrice = config('services.stripe.price_ai_monthly');

    $user->subscriptions()->forceCreate(array_merge([
        'type' => 'default',
        'stripe_id' => 'sub_'.Str::random(10),
        'stripe_status' => 'active',
        'stripe_price' => $stripePrice,
    ], $subAttributes));

    return $user;
}

describe('SubscriptionAccess — active subscription', function () {
    it('grants access for a fully active subscription', function () {
        $user = makeSubscribedUser();

        expect(subscriptionAccess()->hasAccess($user))->toBeTrue();
    });
});

describe('SubscriptionAccess — cancellation period-end access', function () {
    it('grants access through the current paid period after voluntary cancellation', function () {
        $user = makeSubscribedUser([
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(10),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeTrue();
    });

    it('revokes access after the canceled period ends', function () {
        $user = makeSubscribedUser([
            'stripe_status' => 'canceled',
            'ends_at' => now()->subDay(),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeFalse();
    });

    it('does not consult payment-failure grace when granting cancellation period-end access', function () {
        // Voluntary cancellation with ends_at in the future — must NOT be denied
        // by a missing/invalid grace config, proving independence.
        config()->set('billing.payment_failure_grace.incomplete_days');
        config()->set('billing.payment_failure_grace.past_due_days');

        $user = makeSubscribedUser([
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(5),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeTrue();
    });
});

describe('SubscriptionAccess — payment-failure grace half-open boundary (7 days)', function () {
    beforeEach(function () {
        config()->set('billing.payment_failure_grace.incomplete_days', 7);
        config()->set('billing.payment_failure_grace.past_due_days', 7);
    });

    it('grants access inside the seven-day window (1 day elapsed)', function () {
        $user = makeSubscribedUser([
            'stripe_status' => 'incomplete',
            'billing_status_since' => now()->subDay(),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeTrue();
    });

    it('grants access inside the window for past_due status', function () {
        $user = makeSubscribedUser([
            'stripe_status' => 'past_due',
            'billing_status_since' => now()->subDays(6),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeTrue();
    });

    it('suspends access at the exact seven-day boundary (elapsed == days)', function () {
        $user = makeSubscribedUser([
            'stripe_status' => 'past_due',
            'billing_status_since' => now()->subDays(7),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeFalse();
    });

    it('suspends access after the seven-day window expires (8 days elapsed)', function () {
        $user = makeSubscribedUser([
            'stripe_status' => 'incomplete',
            'billing_status_since' => now()->subDays(8),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeFalse();
    });
});

describe('SubscriptionAccess — missing/invalid configuration fails closed', function () {
    it('fails closed (no access) when the grace config is unset', function () {
        config()->set('billing.payment_failure_grace.incomplete_days');
        config()->set('billing.payment_failure_grace.past_due_days', 7);

        $user = makeSubscribedUser([
            'stripe_status' => 'incomplete',
            'billing_status_since' => now()->subDay(),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeFalse();
    });

    it('fails closed when the grace config is non-numeric', function () {
        config()->set('billing.payment_failure_grace.incomplete_days', 'abc');
        config()->set('billing.payment_failure_grace.past_due_days', 7);

        $user = makeSubscribedUser([
            'stripe_status' => 'incomplete',
            'billing_status_since' => now()->subDay(),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeFalse();
    });

    it('fails closed when billing_status_since is missing on an incomplete/past_due sub', function () {
        $user = makeSubscribedUser([
            'stripe_status' => 'past_due',
            'billing_status_since' => null,
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeFalse();
    });
});

describe('SubscriptionAccess — independence from cancellation period-end', function () {
    it('a canceled but not yet ended subscription grants access regardless of payment-failure grace', function () {
        config()->set('billing.payment_failure_grace', ['incomplete_days' => 0, 'past_due_days' => 0]);

        $user = makeSubscribedUser([
            'stripe_status' => 'active',
            'ends_at' => now()->addDays(3),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeTrue();
    });

    it('a canceled past-due subscription checks payment grace, not period-end, when ends_at is in the past', function () {
        // ends_at past: period-end access is over; must fall back to payment-failure grace.
        config()->set('billing.payment_failure_grace.past_due_days', 7);

        $user = makeSubscribedUser([
            'stripe_status' => 'past_due',
            'ends_at' => now()->subDay(),
            'billing_status_since' => now()->subDay(),
        ]);

        expect(subscriptionAccess()->hasAccess($user))->toBeTrue();
    });
});
