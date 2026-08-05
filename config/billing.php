<?php

/*
|--------------------------------------------------------------------------
| Billing configuration
|--------------------------------------------------------------------------
| Payment-failure grace contract. The shipped product default for the grace
| duration is exactly seven (7) days for both `incomplete` and `past_due`.
| An unset or non-numeric runtime value MUST fail closed (no access) rather
| than silently defaulting. The seven-day payment-failure grace is independent
| of, and MUST NOT be conflated with, the period-end access rule for voluntary
| cancellation.
*/

return [
    'payment_failure_grace' => [
        'incomplete_days' => env('BILLING_INCOMPLETE_GRACE_DAYS', 7),
        'past_due_days' => env('BILLING_PAST_DUE_GRACE_DAYS', 7),
    ],

    // Single cache namespace for the unified next-billing-date value consumed
    // by both the manage page and the global Inertia shared props.
    'cache_namespace' => env('BILLING_CACHE_NAMESPACE', 'billing.next_billing_date'),

    // Cache TTL for the billing date when the Stripe API cannot be reached.
    'cache_ttl_minutes' => (int) env('BILLING_CACHE_TTL_MINUTES', 60),
];
