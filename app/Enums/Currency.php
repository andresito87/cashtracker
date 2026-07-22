<?php

namespace App\Enums;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';

    public function symbol(): string
    {
        return match ($this) {
            self::EUR => '€',
            self::USD => '$',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::EUR => __('messages.currency_eur'),
            self::USD => __('messages.currency_usd'),
        };
    }
}
