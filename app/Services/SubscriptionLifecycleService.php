<?php

namespace App\Services;

use App\Models\CheckoutAttempt;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Subscription lifecycle boundary: uniqueness, permitted transitions,
 * recoverable failures, and idempotent checkout.
 *
 * Stripe owns automatic monthly/yearly renewal; the app never creates future
 * periods. Upgrade only monthly→yearly via Cashier `swap`; yearly→monthly is
 * an explicit recoverable error. `cancel()` is period-end; access uses
 * {@see SubscriptionAccess} separately from cancellation.
 */
class SubscriptionLifecycleService
{
    /**
     * Reject checkout when a live, grace, or incomplete subscription exists.
     *
     * @throws SubscriptionConflictException
     */
    public function assertCanCheckout($user): void
    {
        $subscription = $user->subscription();

        if (! $subscription) {
            return;
        }

        $status = (string) $subscription->getAttribute('stripe_status');

        // A live, grace, or incomplete attempt blocks a new checkout.
        $blocking = in_array($status, [
            'active',
            'trialing',
            'past_due',
            'incomplete',
            'canceled',
        ], true);

        if ($blocking && $subscription->active()) {
            throw new SubscriptionConflictException(
                'A subscription already exists for this user.'
            );
        }
    }

    /**
     * Validate the plan transition.
     *
     * @throws SubscriptionTransitionException
     */
    public function assertTransition($user, string $targetPlan): void
    {
        $currentPlan = $this->currentPlanName($user);

        // No current plan: any valid plan is allowed for checkout.
        if (! $currentPlan) {
            return;
        }

        // monthly -> yearly is allowed.
        // yearly -> monthly is forbidden.
        if ($currentPlan === 'yearly' && $targetPlan === 'monthly') {
            throw new SubscriptionTransitionException(
                'Downgrade from yearly to monthly is not allowed.'
            );
        }
    }

    /**
     * Store a checkout-attempt receipt for idempotent replay.
     *
     * @throws SubscriptionStorageException
     */
    public function storeReceipt(int $userId, string $plan, ?string $idempotencyKey, string $sessionId, string $url): void
    {
        try {
            DB::transaction(function () use ($userId, $plan, $idempotencyKey, $sessionId, $url) {
                CheckoutAttempt::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'idempotency_key' => $idempotencyKey,
                    ],
                    [
                        'plan' => $plan,
                        'stripe_session_id' => $sessionId,
                        'stripe_url' => $url,
                        'status' => 'pending',
                    ]
                );
            });
        } catch (Throwable $e) {
            throw new SubscriptionStorageException('Failed to store checkout attempt receipt.', previous: $e);
        }
    }

    /**
     * Find an existing receipt for an idempotent replay (double-submit).
     */
    public function findReceipt(int $userId, ?string $idempotencyKey): ?CheckoutAttempt
    {
        if (! $idempotencyKey) {
            return null;
        }

        return CheckoutAttempt::where('user_id', $userId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    /**
     * Resolve the current plan name ('monthly' or 'yearly') for the user.
     */
    private function currentPlanName($user): ?string
    {
        $subscription = $user->subscription();

        if (! $subscription || ! $subscription->active()) {
            return null;
        }

        $price = $subscription->stripe_price;

        $yearlyId = config('plans.currencies_plans.EUR.yearly.stripe_price_id')
            ?? config('services.stripe.prices.EUR.yearly')
            ?? config('services.stripe.price_ai_yearly');
        $monthlyId = config('plans.currencies_plans.EUR.monthly.stripe_price_id')
            ?? config('services.stripe.prices.EUR.monthly')
            ?? config('services.stripe.price_ai_monthly');

        if ($price === $yearlyId) {
            return 'yearly';
        }

        if ($price === $monthlyId) {
            return 'monthly';
        }

        return null;
    }
}
