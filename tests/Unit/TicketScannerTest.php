<?php

use App\Ai\Agents\TicketScanner;
use App\Enums\Currency;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;

describe('TicketScanner Agent Unit Tests', function () {
    it('initializes with default EUR currency when no currency is passed', function () {
        $agent = new TicketScanner;

        $instructions = (string) $agent->instructions();

        expect($instructions)->toContain('EUR')
            ->and($instructions)->toContain('€');
    });

    it('customizes instructions with target currency USD when passed', function () {
        $agent = new TicketScanner(Currency::USD);

        $instructions = (string) $agent->instructions();

        expect($instructions)->toContain('USD')
            ->and($instructions)->toContain('$');
    });

    it('includes strict store naming rules prohibiting waiter or cashier names like Admin', function () {
        $agent = new TicketScanner;
        $instructions = (string) $agent->instructions();

        expect($instructions)->toContain('JAMÁS uses nombres de cajeros, meseros, empleados')
            ->and($instructions)->toContain('"Admin"')
            ->and($instructions)->toContain('"store"');
    });

    it('includes decimal conversion and item extraction rules in instructions', function () {
        $agent = new TicketScanner;
        $instructions = (string) $agent->instructions();

        expect($instructions)->toContain('MAYOR A 0 (> 0)')
            ->and($instructions)->toContain('Ignora completamente aquellos ítems o sublíneas cuyo importe sea 0.00')
            ->and($instructions)->toContain('Convierte siempre cualquier coma decimal (",") a punto decimal (".")');
    });

    it('returns structured JSON schema containing store, category, and items', function () {
        $agent = new TicketScanner;
        $factory = new JsonSchemaTypeFactory;

        $schema = $agent->schema($factory);

        expect($schema)->toHaveKeys(['store', 'category', 'items']);
    });
});
