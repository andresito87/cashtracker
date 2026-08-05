<?php

use App\Services\BillingCatalog;

function billingCatalog(): BillingCatalog
{
    return app(BillingCatalog::class);
}

describe('BillingCatalog — config read and version', function () {
    it('returns a version string from the catalog configuration', function () {
        config()->set('plans.version', '2026-08-01');

        expect(billingCatalog()->version())->toBe('2026-08-01');
    });

    it('reads monthly and yearly plans for EUR with integer cents and stripe price ids', function () {
        config()->set('plans', [
            'version' => '2026-08-01',
            'currency' => 'EUR',
            'currencies' => [
                'EUR' => ['symbol' => '€', 'spanish_locale' => true],
            ],
            'plans' => [
                'monthly' => [
                    'stripe_price_id' => 'price_eur_monthly',
                    'price_minor' => 3900,
                    'capabilities' => ['ai', 'ticket_scan'],
                    'ai_limits' => null,
                ],
                'yearly' => [
                    'stripe_price_id' => 'price_eur_yearly',
                    'price_minor' => 29900,
                    'capabilities' => ['ai', 'ticket_scan', 'priority_support'],
                    'ai_limits' => ['messages' => 1000],
                ],
            ],
        ]);

        $catalog = billingCatalog()->forCurrency('EUR');

        expect($catalog)->toHaveKey('version')
            ->and($catalog['version'])->toBe('2026-08-01')
            ->and($catalog['currency'])->toBe('EUR')
            ->and($catalog['plans'])->toHaveKeys(['monthly', 'yearly'])
            ->and($catalog['plans']['monthly']['price_minor'])->toBe(3900)
            ->and($catalog['plans']['monthly']['stripe_price_id'])->toBe('price_eur_monthly')
            ->and($catalog['plans']['yearly']['price_minor'])->toBe(29900)
            ->and($catalog['plans']['monthly']['capabilities'])->toBe(['ai', 'ticket_scan'])
            ->and($catalog['plans']['yearly']['ai_limits'])->toBe(['messages' => 1000]);
    });
});

describe('BillingCatalog — integer-cents formatting (no float arithmetic)', function () {
    it('formats a monthly price into a localized display string without float math', function () {
        config()->set('plans', [
            'version' => 'v1',
            'currency' => 'EUR',
            'currencies' => [
                'EUR' => ['symbol' => '€', 'spanish_locale' => true],
            ],
            'plans' => [
                'monthly' => ['stripe_price_id' => 'pm', 'price_minor' => 3900, 'capabilities' => []],
                'yearly' => ['stripe_price_id' => 'py', 'price_minor' => 29900, 'capabilities' => []],
            ],
        ]);

        $catalog = billingCatalog()->forCurrency('EUR');

        // 3900 cents -> 39.00; formatted via integer division, no float multiplication.
        expect($catalog['plans']['monthly']['display_price'])->toBe('39,00€')
            ->and($catalog['plans']['yearly']['display_price'])->toBe('299,00€');
    });

    it('formats a USD price with the dollar symbol and dot decimal separator', function () {
        config()->set('plans', [
            'version' => 'v1',
            'currency' => 'USD',
            'currencies' => [
                'EUR' => ['symbol' => '€', 'spanish_locale' => true],
                'USD' => ['symbol' => '$', 'spanish_locale' => false],
            ],
            'plans' => [
                'monthly' => ['stripe_price_id' => 'pm', 'price_minor' => 3900, 'capabilities' => []],
                'yearly' => ['stripe_price_id' => 'py', 'price_minor' => 29900, 'capabilities' => []],
            ],
        ]);

        $catalog = billingCatalog()->forCurrency('USD');

        expect($catalog['plans']['monthly']['display_price'])->toBe('$39.00')
            ->and($catalog['plans']['yearly']['display_price'])->toBe('$299.00');
    });

    it('derives monthly-equivalent from yearly via integer cents division, no float', function () {
        config()->set('plans', [
            'version' => 'v1',
            'currency' => 'EUR',
            'currencies' => [
                'EUR' => ['symbol' => '€', 'spanish_locale' => true],
            ],
            'plans' => [
                'monthly' => ['stripe_price_id' => 'pm', 'price_minor' => 3900, 'capabilities' => []],
                'yearly' => ['stripe_price_id' => 'py', 'price_minor' => 29900, 'capabilities' => []],
            ],
        ]);

        $catalog = billingCatalog()->forCurrency('EUR');

        // monthly-equivalent = 29900 / 12 = 2491.67 cents (integer division with remainder).
        expect($catalog['plans']['yearly']['monthly_equivalent_minor'])->toBe(2492)
            ->and($catalog['plans']['yearly']['monthly_equivalent_display'])->toBe('24,92€');
    });
});

describe('BillingCatalog — shared props for Inertia', function () {
    it('produces a JSON-safe shared props shape with version, currency, and plans', function () {
        config()->set('plans', [
            'version' => 'v1',
            'currency' => 'EUR',
            'currencies' => [
                'EUR' => ['symbol' => '€', 'spanish_locale' => true],
            ],
            'plans' => [
                'monthly' => ['stripe_price_id' => 'pm', 'price_minor' => 3900, 'capabilities' => ['ai'], 'ai_limits' => null],
                'yearly' => ['stripe_price_id' => 'py', 'price_minor' => 29900, 'capabilities' => ['ai'], 'ai_limits' => ['messages' => 1000]],
            ],
        ]);

        $props = billingCatalog()->sharedProps('EUR');

        expect($props)->toHaveKeys(['version', 'currency', 'plans'])
            ->and($props['plans']['monthly'])->toHaveKeys(['price_minor', 'display_price', 'capabilities', 'ai_limits', 'stripe_price_id'])
            ->and(json_encode($props))->toBeString();
    });
});
