<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Laravel\Cashier\Subscription;

/**
 * Resolves whether a user has premium access.
 *
 * The payment-failure grace window is independent of the period-end access rule
 * for voluntary cancellation. A voluntarily canceled subscription with a future
 * `ends_at` grants access through the end of the paid period regardless of any
 * payment-failure grace setting. For `incomplete`/`past_due` statuses, access is
 * granted only while the elapsed time since `billing_status_since` is strictly
 * less than the configured duration (`now < billing_status_since + days`); at
 * the exact boundary it is suspended. An unset or non-numeric configuration
 * fails closed (no access).
 */
class SubscriptionAccess
{
    /**
     * Determine whether the user has premium access for gating.
     */
    public function hasAccess(User $user): bool
    {
        $subscription = $user->subscription();

        if (! $subscription) {
            return false;
        }

        $status = (string) $subscription->getAttribute('stripe_status');

        // Voluntary cancellation period-end access: independent of payment-failure
        // grace. Cashier's canceled-but-on-grace subs have a future `ends_at`.
        // This MUST be evaluated before the payment-failure path and MUST NOT
        // consult payment-failure grace.
        if ($this->hasPaidPeriodAccess($subscription)) {
            return true;
        }

        // incomplete/past_due are payment-failure statuses: evaluate the
        // configurable grace window. We deliberately do NOT rely on Cashier's
        // active() here, which would unconditionally treat past_due as active.
        if (in_array($status, ['incomplete', 'past_due'], true)) {
            return $this->hasPaymentFailureGrace($subscription);
        }

        return $subscription->active();
    }

    /**
     * Independent paid-period access for a voluntary cancellation before ends_at.
     * Does NOT consult payment-failure grace.
     */
    private function hasPaidPeriodAccess(Subscription $subscription): bool
    {
        $endsAt = $this->endsAt($subscription);

        if (! $endsAt) {
            return false;
        }

        // Only a future ends_at grants access through the current paid period.
        // A past ends_at means the period has ended; access is revoked here and
        // falls through to the payment-failure grace evaluation.
        return $endsAt > CarbonImmutable::now();
    }

    /**
     * Evaluate payment-failure grace for incomplete/past_due statuses using the
     * half-open comparison `elapsed < configured_days`.
     */
    private function hasPaymentFailureGrace(Subscription $subscription): bool
    {
        $status = (string) $subscription->getAttribute('stripe_status');

        if (! in_array($status, ['incomplete', 'past_due'], true)) {
            return false;
        }

        $days = $this->graceDaysFor($status);

        // Missing or non-numeric configuration fails closed.
        if ($days === null) {
            return false;
        }

        $since = $this->billingStatusSince($subscription);

        // Missing anchor on a failure status fails closed.
        if (! $since) {
            return false;
        }

        $boundary = $since->addDays($days);

        // Half-open: access only while now < boundary; at the exact boundary suspended.
        return CarbonImmutable::now() < $boundary;
    }

    /**
     * Resolve the configured grace days for a status, or null if missing/invalid.
     */
    private function graceDaysFor(string $status): ?int
    {
        $key = $status === 'incomplete' ? 'incomplete_days' : 'past_due_days';
        $value = config("billing.payment_failure_grace.$key");

        if (! is_numeric($value)) {
            return null;
        }

        $days = (int) $value;

        // Negative durations are invalid and fail closed.
        return $days >= 0 ? $days : null;
    }

    private function endsAt(Subscription $subscription): ?CarbonImmutable
    {
        $endsAt = $subscription->getAttribute('ends_at');

        return $endsAt ? $this->toCarbon($endsAt) : null;
    }

    private function billingStatusSince(Subscription $subscription): ?CarbonImmutable
    {
        $since = $subscription->getAttribute('billing_status_since');

        return $since ? $this->toCarbon($since) : null;
    }

    private function toCarbon(mixed $date): CarbonImmutable
    {
        if ($date instanceof DateTimeInterface) {
            return CarbonImmutable::instance($date);
        }

        return CarbonImmutable::parse((string) $date);
    }
}
