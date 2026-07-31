<?php

use App\Enums\Currency;

test('currency enum returns correct symbols', function () {
    expect(Currency::EUR->symbol())->toBe('€')
        ->and(Currency::USD->symbol())->toBe('$');
});

test('currency enum returns correct localized labels in Spanish', function () {
    app()->setLocale('es');

    expect(Currency::EUR->label())->toBe(__('messages.currency_eur'))
        ->and(Currency::USD->label())->toBe(__('messages.currency_usd'));
});

test('currency enum returns correct localized labels in English', function () {
    app()->setLocale('en');

    expect(Currency::EUR->label())->toBe(__('messages.currency_eur'))
        ->and(Currency::USD->label())->toBe(__('messages.currency_usd'));
});
