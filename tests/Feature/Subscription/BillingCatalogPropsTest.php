<?php

use App\Enums\Currency;
use Inertia\Testing\AssertableInertia as Assert;

describe('Billing catalog shared props', function () {
    it('shares the versioned catalog in Inertia props for an authenticated user', function () {
        actingAsVerifiedUser(['currency' => Currency::EUR]);

        $this->get(route('subscription.manage'))
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->has('catalog')
                ->where('catalog.currency', 'EUR')
                ->where('catalog.version', config('plans.version'))
                ->has('catalog.plans.monthly.price_minor')
                ->has('catalog.plans.monthly.display_price')
                ->has('catalog.plans.yearly.price_minor')
                ->has('catalog.plans.yearly.display_price')
            );
    });

    it('shares the catalog keyed by the user currency for USD', function () {
        actingAsVerifiedUser(['currency' => Currency::USD]);

        $this->get(route('subscription.manage'))
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->where('catalog.currency', 'USD')
            );
    });
});
