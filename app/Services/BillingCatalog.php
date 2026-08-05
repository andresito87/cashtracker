<?php

namespace App\Services;

/**
 * Versioned billing plan catalog.
 *
 * Treats the Laravel versioned plan configuration as the single authoritative
 * source for plan identifiers, Stripe price IDs, display prices (integer
 * minor units / cents), currency, and capabilities. Formats display prices at
 * render time using integer division only — no float arithmetic is performed on
 * prices anywhere.
 */
class BillingCatalog
{
    /**
     * The catalog version, used by React consumers to detect changes.
     */
    public function version(): string
    {
        return (string) config('plans.version', 'unknown');
    }

    /**
     * Resolve a plan's Stripe price ID for the given currency.
     */
    public function priceIdFor(string $plan, ?string $currency = null): ?string
    {
        $currency = $this->normalizeCurrency($currency);

        $perCurrency = config("plans.currencies_plans.$currency.$plan.stripe_price_id");

        if ($perCurrency) {
            return $perCurrency;
        }

        return config("plans.plans.$plan.stripe_price_id");
    }

    /**
     * Build the catalog payload for a given currency, with display prices
     * formatted from integer cents.
     *
     * @return array{version: string, currency: string, plans: array<string, array<string, mixed>>}
     */
    public function forCurrency(?string $currency = null): array
    {
        $currency = $this->normalizeCurrency($currency);
        $meta = config("plans.currencies.$currency", ['symbol' => '€', 'spanish_locale' => true]);
        $symbol = $meta['symbol'] ?? '€';
        $spanish = (bool) ($meta['spanish_locale'] ?? true);

        $plans = [];
        foreach (config('plans.plans', []) as $name => $plan) {
            $priceMinor = $this->priceMinorFor($name, $currency);
            $plans[$name] = [
                'stripe_price_id' => $this->priceIdFor($name, $currency),
                'price_minor' => $priceMinor,
                'display_price' => $this->formatCents($priceMinor, $symbol, $spanish),
                'capabilities' => $plan['capabilities'] ?? [],
                'ai_limits' => $plan['ai_limits'] ?? null,
            ];

            if ($name === 'yearly') {
                $equiv = $this->monthlyEquivalentMinor($priceMinor);
                $plans[$name]['monthly_equivalent_minor'] = $equiv;
                $plans[$name]['monthly_equivalent_display'] = $this->formatCents($equiv, $symbol, $spanish);
                $plans[$name]['monthly_total_minor'] = ($this->priceMinorFor('monthly', $currency)) * 12;
                $plans[$name]['savings_minor'] = ($this->priceMinorFor('monthly', $currency)) * 12 - $priceMinor;
            }
        }

        return [
            'version' => $this->version(),
            'currency' => $currency,
            'plans' => $plans,
        ];
    }

    /**
     * The Inertia shared-props payload for React consumption.
     *
     * @return array{version: string, currency: string, plans: array<string, array<string, mixed>>}
     */
    public function sharedProps(?string $currency = null): array
    {
        return $this->forCurrency($currency);
    }

    /**
     * Format integer cents into a localized display string using integer
     * division only. No float arithmetic is performed.
     */
    public function formatCents(int $cents, string $symbol, bool $spanish): string
    {
        $whole = intdiv($cents, 100);
        $fraction = $cents % 100;

        $decimalSeparator = $spanish ? ',' : '.';
        $thousandsSeparator = $spanish ? '.' : ',';

        // Build the integer part with thousands separators via number_format on
        // a whole-number only (no fractional float), then append the two-digit
        // fraction parsed from the integer remainder — avoiding any float math.
        $integerPart = number_format($whole, 0, '', $thousandsSeparator);
        $fractionPart = str_pad((string) $fraction, 2, '0', STR_PAD_LEFT);

        $formatted = $integerPart.$decimalSeparator.$fractionPart;

        return $spanish ? $formatted.$symbol : $symbol.$formatted;
    }

    /**
     * Compute the monthly-equivalent in integer cents (round-half-up on the
     * third decimal) using integer arithmetic only.
     */
    public function monthlyEquivalentMinor(int $yearlyMinor): int
    {
        // yearlyMinor / 12, round-half-up at the cent (third digit of the
        // implicit decimal: remainder*1000 / 12, third digit >= 5 rounds up).
        $cents = intdiv($yearlyMinor, 12);
        $remainder = $yearlyMinor % 12;
        // remainder/12 in cents — third decimal drives rounding.
        $thirdDigit = intdiv($remainder * 1000, 12) % 10;

        return $cents + ($thirdDigit >= 5 ? 1 : 0);
    }

    private function priceMinorFor(string $plan, string $currency): int
    {
        $perCurrency = config("plans.currencies_plans.$currency.$plan.price_minor");

        if (is_numeric($perCurrency)) {
            return (int) $perCurrency;
        }

        return (int) config("plans.plans.$plan.price_minor", 0);
    }

    private function normalizeCurrency(?string $currency): string
    {
        $currency = $currency ?: config('plans.currency', 'EUR');

        return in_array($currency, ['EUR', 'USD'], true) ? $currency : 'EUR';
    }
}
