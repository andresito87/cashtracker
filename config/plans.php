<?php

/*
|--------------------------------------------------------------------------
| Versioned billing plan catalog
|--------------------------------------------------------------------------
| The single authoritative source for plan identifiers, Stripe price IDs,
| display prices (integer minor units / cents), currency, and capabilities.
| React consumers receive this catalog via Inertia shared props and MUST NOT
| hardcode prices or perform float arithmetic. Bump the version field when the
| catalog changes so React consumers can detect updates.
*/

return [
    'version' => env('PLANS_CATALOG_VERSION', '2026-08-01'),

    // Default catalog currency; per-currency overrides live under `currencies`.
    'currency' => 'EUR',

    'currencies' => [
        'EUR' => [
            'symbol' => '€',
            'spanish_locale' => true,
        ],
        'USD' => [
            'symbol' => '$',
            'spanish_locale' => false,
        ],
    ],

    'plans' => [
        'monthly' => [
            'stripe_price_id' => env('STRIPE_PRICE_EUR_MONTHLY', env('STRIPE_PRICE_AI_MONTHLY')),
            'price_minor' => (int) env('PLANS_MONTHLY_PRICE_MINOR', 3900),
            'capabilities' => ['ai', 'ticket_scan', 'cancel_anytime'],
            'ai_limits' => null,
        ],
        'yearly' => [
            'stripe_price_id' => env('STRIPE_PRICE_EUR_YEARLY', env('STRIPE_PRICE_AI_YEARLY')),
            'price_minor' => (int) env('PLANS_YEARLY_PRICE_MINOR', 29900),
            'capabilities' => ['ai', 'ticket_scan', 'cancel_anytime', 'priority_support', 'free_months'],
            'ai_limits' => ['messages' => (int) env('PLANS_YEARLY_AI_MESSAGES', 1000)],
        ],
    ],

    'currencies_plans' => [
        'EUR' => [
            'monthly' => [
                'stripe_price_id' => env('STRIPE_PRICE_EUR_MONTHLY', env('STRIPE_PRICE_AI_MONTHLY')),
                'price_minor' => (int) env('PLANS_EUR_MONTHLY_PRICE_MINOR', 3900),
            ],
            'yearly' => [
                'stripe_price_id' => env('STRIPE_PRICE_EUR_YEARLY', env('STRIPE_PRICE_AI_YEARLY')),
                'price_minor' => (int) env('PLANS_EUR_YEARLY_PRICE_MINOR', 29900),
            ],
        ],
        'USD' => [
            'monthly' => [
                'stripe_price_id' => env('STRIPE_PRICE_USD_MONTHLY', env('STRIPE_PRICE_AI_MONTHLY')),
                'price_minor' => (int) env('PLANS_USD_MONTHLY_PRICE_MINOR', 3900),
            ],
            'yearly' => [
                'stripe_price_id' => env('STRIPE_PRICE_USD_YEARLY', env('STRIPE_PRICE_AI_YEARLY')),
                'price_minor' => (int) env('PLANS_USD_YEARLY_PRICE_MINOR', 29900),
            ],
        ],
    ],
];
