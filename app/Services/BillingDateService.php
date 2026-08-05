<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Subscription;
use Throwable;

/**
 * Unified next-billing-date behavior with one cache namespace.
 *
 * Both the manage page and the global Inertia props consume the same value.
 * `for(User)` computes and caches the next billing date; `forget()` invalidates
 * it after local transitions and webhook events. The cache stores a serialized
 * date string; the returned value is always a {@see CarbonImmutable}.
 */
class BillingDateService
{
    /**
     * Compute and cache the next billing date for the user's subscription.
     */
    public function for(User $user): ?CarbonImmutable
    {
        $subscription = $user->subscription();

        if (! $subscription) {
            return null;
        }

        $key = $this->cacheKey($user);

        if (Cache::has($key)) {
            return $this->toCarbon(Cache::get($key));
        }

        $date = $this->compute($subscription);
        config('billing.cache_ttl_minutes', 60)
			? Cache::put($key, $date?->toDateTimeString(), now()->addMinutes(config('billing.cache_ttl_minutes', 60)))
			: Cache::forever($key, $date?->toDateTimeString());

        return $date;
    }

    /**
     * Invalidate the cached billing date for a user.
     */
    public function forget(User $user): void
    {
        Cache::forget($this->cacheKey($user));
    }

    /**
     * Invalidate the cached billing date for the user behind a subscription.
     */
    public function forgetForSubscription(Subscription $subscription): void
    {
        $user = $subscription->user()->first();

        if ($user instanceof User) {
            $this->forget($user);
        }
    }

    /**
     * The single cache key for the user under the configured namespace.
     */
    public function cacheKey(User $user): string
    {
        return config('billing.cache_namespace', 'billing.next_billing_date').':'.$user->id;
    }

    /**
     * Compute the next billing date from a subscription.
     * Uses ends_at for canceled subs, otherwise the current period end from Stripe.
     */
    private function compute(Subscription $subscription): ?CarbonImmutable
    {
        $endsAt = $subscription->getAttribute('ends_at');

        if ($endsAt) {
            return $this->toCarbon($endsAt);
        }

        try {
            $stripe = $subscription->asStripeSubscription();
            $periodEnd = $stripe->current_period_end
                ?? ($stripe->items->data[0]->current_period_end ?? null);

            return $periodEnd
                ? CarbonImmutable::createFromTimestamp($periodEnd)
                : $this->fallbackFromCreatedAt($subscription);
        } catch (Throwable) {
            return $this->fallbackFromCreatedAt($subscription);
        }
    }

    private function fallbackFromCreatedAt(Subscription $subscription): ?CarbonImmutable
    {
        $createdAt = $subscription->getAttribute('created_at');

        return $createdAt
            ? $this->toCarbon($createdAt)->addMonth()
            : null;
    }

    private function toCarbon(mixed $date): CarbonImmutable
    {
        if ($date instanceof DateTimeInterface) {
            return CarbonImmutable::instance($date);
        }

        return CarbonImmutable::parse((string) $date);
    }
}
