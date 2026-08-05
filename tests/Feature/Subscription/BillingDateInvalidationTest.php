<?php

use App\Enums\Currency;
use App\Models\User;
use App\Services\BillingDateService;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Cashier\Subscription;

function makeUserForInvalidation(): User
{
    return User::factory()->create(['currency' => Currency::EUR]);
}

describe('BillingDateService cache invalidation on transitions', function () {
    it('forwards cache invalidation through the service for a user', function () {
        $user = makeUserForInvalidation();
        $service = app(BillingDateService::class);

        // Seed the cache key directly so the service-level forget is observable
        // without requiring a real Stripe subscription lookup.
        cache()->put($service->cacheKey($user), '2026-12-31', now()->addHour());
        expect(cache()->has($service->cacheKey($user)))->toBeTrue();

        $service->forget($user);
        expect(cache()->has($service->cacheKey($user)))->toBeFalse();
    });

    it('invalidates the cached billing date when the user cancels via the controller', function () {
        $user = makeUserForInvalidation();

        $fakeSub = new class extends Subscription
        {
            public function cancel(): static
            {
                $this->forceFill([
                    'stripe_status' => 'canceled',
                    'ends_at' => now()->addMonth(),
                ]);

                return $this;
            }
        };
        $fakeSub->forceFill(['type' => 'default', 'stripe_status' => 'active']);
        $user->setRelation('subscriptions', new Collection([$fakeSub]));

        $service = app(BillingDateService::class);
        cache()->put($service->cacheKey($user), '2026-12-31', now()->addHour());
        expect(cache()->has($service->cacheKey($user)))->toBeTrue();

        $this->actingAs($user)->post(route('subscription.cancel'))
            ->assertRedirect();

        expect(cache()->has($service->cacheKey($user)))->toBeFalse();
    });

    it('invalidates the cached billing date when the user resumes via the controller', function () {
        $user = makeUserForInvalidation();

        $fakeSub = new class extends Subscription
        {
            public bool $resumed = false;

            public function onGracePeriod(): bool
            {
                return true;
            }

            public function resume(): static
            {
                $this->resumed = true;

                return $this;
            }
        };
        $fakeSub->forceFill(['type' => 'default', 'stripe_status' => 'active']);
        $user->setRelation('subscriptions', new Collection([$fakeSub]));

        $service = app(BillingDateService::class);
        cache()->put($service->cacheKey($user), '2026-12-31', now()->addHour());
        expect(cache()->has($service->cacheKey($user)))->toBeTrue();

        $this->actingAs($user)->post(route('subscription.resume'))
            ->assertRedirect();

        expect($fakeSub->resumed)->toBeTrue()
            ->and(cache()->has($service->cacheKey($user)))->toBeFalse();
    });
});
