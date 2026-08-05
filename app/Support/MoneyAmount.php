<?php

namespace App\Support;

/**
 * Exact money normalization value object.
 *
 * Stores integer cents internally to avoid PHP/JSON float ambiguity. Accepts a
 * decimal string only at boundaries, rejects scientific notation lexically,
 * normalizes with round-half-up (third digit >= 5), then enforces the
 * 1..9,999,999,999 cent range (0.01 to 99999999.99).
 */
final class MoneyAmount
{
    /** Minimum allowed cents (0.01). */
    public const int MIN_CENTS = 1;

    /** Maximum allowed cents (99999999.99). */
    public const int MAX_CENTS = 9999999999;

    private function __construct(private readonly int $cents)
    {
        if ($cents < 0 || $cents > self::MAX_CENTS) {
            throw new InvalidMoney('Amount cents must be between 0 and 9999999999.');
        }
    }

    /**
     * Parse a decimal string into integer cents with round-half-up normalization.
     *
     * @param  string  $value  Decimal string such as `12.34`. Floats and scientific
     *                         notation are rejected to avoid binary ambiguity.
     */
    public static function fromString(string $value): self
    {
        $cents = self::parseCents($value);

        if ($cents < self::MIN_CENTS) {
            throw new InvalidMoney('Amount must be at least 0.01.');
        }

        return new self($cents);
    }

    /**
     * Parse a decimal string into integer cents with round-half-up normalization,
     * allowing zero. Use this for derived sums (e.g. active expense total, budget
     * amount) where zero is a valid value; use {@see fromString} for user input
     * where the amount must be strictly positive.
     */
    public static function parseCents(string $value): int
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            throw new InvalidMoney('Amount must be a non-empty decimal string.');
        }

        // Reject scientific notation lexically (e.g., 1e3, 2.5E-2, 1e+3).
        if (preg_match('/[eE]/', $trimmed)) {
            throw new InvalidMoney('Scientific notation is not allowed for money amounts.');
        }

        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $trimmed)) {
            throw new InvalidMoney('Amount must be a decimal number string.');
        }

        if (str_starts_with($trimmed, '-')) {
            throw new InvalidMoney('Amount must be positive.');
        }

        $parts = explode('.', $trimmed);
        $integerPart = ltrim($parts[0], '0') ?: '0';
        $fractionPart = $parts[1] ?? '';

        $fractionPadded = str_pad($fractionPart, 3, '0');

        $integerCents = ((int) $integerPart) * 100;
        $firstTwoDigits = (int) substr($fractionPadded, 0, 2);
        $thirdDigit = (int) substr($fractionPadded, 2, 1);

        return $integerCents + $firstTwoDigits + ($thirdDigit >= 5 ? 1 : 0);
    }

    /**
     * Build a MoneyAmount from integer cents.
     */
    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    /**
     * The integer cents value.
     */
    public function cents(): int
    {
        return $this->cents;
    }

    /**
     * The canonical two-decimal string, e.g. `12.34`.
     */
    public function canonical(): string
    {
        return number_format($this->cents / 100, 2, '.', '');
    }

    /**
     * Format with a currency symbol and locale-aware separators.
     *
     * @param  string  $symbol  Currency symbol, e.g. `€` or `$`.
     * @param  bool  $spanishLocale  When true, use `1.234,56€` formatting; otherwise `$1,234.56`.
     */
    public function formatted(string $symbol, bool $spanishLocale): string
    {
        $decimalSeparator = $spanishLocale ? ',' : '.';
        $thousandsSeparator = $spanishLocale ? '.' : ',';

        $formatted = number_format($this->cents / 100, 2, $decimalSeparator, $thousandsSeparator);

        return $spanishLocale ? $formatted.$symbol : $symbol.$formatted;
    }
}
