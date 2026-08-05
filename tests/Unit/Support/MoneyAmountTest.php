<?php

use App\Support\InvalidMoney;
use App\Support\MoneyAmount;

describe('MoneyAmount — parsing', function () {
    it('accepts a two-decimal string and stores integer cents', function () {
        $money = MoneyAmount::fromString('12.34');

        expect($money->cents())->toBe(1234)
            ->and($money->canonical())->toBe('12.34');
    });

    it('accepts a whole-number amount', function () {
        expect(MoneyAmount::fromString('40')->cents())->toBe(4000)
            ->and(MoneyAmount::fromString('40')->canonical())->toBe('40.00');
    });

    it('accepts a single-decimal amount and pads to two decimals', function () {
        expect(MoneyAmount::fromString('7.5')->cents())->toBe(750)
            ->and(MoneyAmount::fromString('7.5')->canonical())->toBe('7.50');
    });
});

describe('MoneyAmount — round-half-up normalization', function () {
    it('rounds the third decimal up when >= 5', function () {
        expect(MoneyAmount::fromString('45.501')->canonical())->toBe('45.50')
            ->and(MoneyAmount::fromString('45.505')->canonical())->toBe('45.51')
            ->and(MoneyAmount::fromString('0.005')->canonical())->toBe('0.01')
            ->and(MoneyAmount::fromString('0.009')->canonical())->toBe('0.01');
    });

    it('rounds the third decimal down when < 5', function () {
        expect(MoneyAmount::fromString('45.504')->canonical())->toBe('45.50')
            ->and(MoneyAmount::fromString('0.333')->canonical())->toBe('0.33')
            ->and(MoneyAmount::fromString('0.334')->canonical())->toBe('0.33');
    });

    it('preserves the sum of rounded per-item amounts (three items of 0.333)', function () {
        $items = [MoneyAmount::fromString('0.333'), MoneyAmount::fromString('0.333'), MoneyAmount::fromString('0.333')];

        $sum = array_reduce($items, fn (int $carry, MoneyAmount $m) => $carry + $m->cents(), 0);

        // Each rounds to 0.33 cents; total is 0.99 — exactly the sum of persisted amounts.
        expect($sum)->toBe(99);
    });
});

describe('MoneyAmount — scientific notation rejection', function () {
    it('rejects lowercase exponent notation', function () {
        expect(fn () => MoneyAmount::fromString('1e3'))->toThrow(InvalidMoney::class);
    });

    it('rejects uppercase exponent notation', function () {
        expect(fn () => MoneyAmount::fromString('2.5E-2'))->toThrow(InvalidMoney::class);
    });

    it('rejects exponent with plus sign', function () {
        expect(fn () => MoneyAmount::fromString('1e+3'))->toThrow(InvalidMoney::class);
    });
});

describe('MoneyAmount — amount limits', function () {
    it('rejects amounts above the 99999999.99 cap', function () {
        expect(fn () => MoneyAmount::fromString('100000000.00'))->toThrow(InvalidMoney::class);
    });

    it('rejects a cap that would round into overflow (99999999.995)', function () {
        expect(fn () => MoneyAmount::fromString('99999999.995'))->toThrow(InvalidMoney::class);
    });

    it('accepts the exact cap 99999999.99', function () {
        expect(MoneyAmount::fromString('99999999.99')->cents())->toBe(9999999999);
    });

    it('rejects zero', function () {
        expect(fn () => MoneyAmount::fromString('0'))->toThrow(InvalidMoney::class)
            ->and(fn () => MoneyAmount::fromString('0.00'))->toThrow(InvalidMoney::class);
    });

    it('rejects negative amounts', function () {
        expect(fn () => MoneyAmount::fromString('-1.00'))->toThrow(InvalidMoney::class);
    });

    it('accepts the minimum 0.01', function () {
        expect(MoneyAmount::fromString('0.01')->cents())->toBe(1);
    });
});

describe('MoneyAmount — rejection of non-decimal input', function () {
    it('rejects non-numeric strings', function () {
        expect(fn () => MoneyAmount::fromString('not-a-number'))->toThrow(InvalidMoney::class)
            ->and(fn () => MoneyAmount::fromString(''))->toThrow(InvalidMoney::class)
            ->and(fn () => MoneyAmount::fromString('  '))->toThrow(InvalidMoney::class);
    });

    it('rounds a long decimal string deterministically without a float binary path', function () {
        // The string is parsed digit-by-digit into integer cents; no float arithmetic
        // is performed, so binary ambiguity cannot leak into the stored value.
        expect(MoneyAmount::fromString((string) (1 / 3))->canonical())->toBe('0.33')
            ->and(MoneyAmount::fromString('0.335')->canonical())->toBe('0.34');
    });
});

describe('MoneyAmount — formatting helpers', function () {
    it('formats cents into a fixed two-decimal string', function () {
        expect(MoneyAmount::fromCents(1234)->canonical())->toBe('12.34')
            ->and(MoneyAmount::fromCents(5)->canonical())->toBe('0.05')
            ->and(MoneyAmount::fromCents(0)->canonical())->toBe('0.00');
    });

    it('formats with a currency symbol and locale separators', function () {
        $money = MoneyAmount::fromCents(123456);

        expect($money->formatted('€', true))->toBe('1.234,56€')
            ->and($money->formatted('$', false))->toBe('$1,234.56');
    });
});
