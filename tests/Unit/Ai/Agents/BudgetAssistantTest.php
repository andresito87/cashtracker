<?php

use App\Ai\Agents\BudgetAssistant;
use App\Ai\Tools\AddExpense;
use App\Ai\Tools\SearchExpenses;

describe('BudgetAssistant agent configuration', function () {
    it('defaults budgetId to zero', function () {
        $agent = new BudgetAssistant;

        expect($agent->budgetId)->toBe(0);
    });

    it('defaults budgetContext to an empty string', function () {
        $agent = new BudgetAssistant;

        expect($agent->budgetContext)->toBe('');
    });

    it('interpolates budgetContext into instructions', function () {
        $agent = new BudgetAssistant;
        $agent->budgetContext = 'Este presupuesto es de tipo general llamado Vacaciones con 500€.';

        expect($agent->instructions())->toContain($agent->budgetContext);
    });

    it('returns exactly two tools', function () {
        $agent = new BudgetAssistant;
        $agent->budgetId = 42;

        expect($agent->tools())->toHaveCount(2);
    });

    it('includes a SearchExpenses tool wired to the correct budgetId', function () {
        $agent = new BudgetAssistant;
        $agent->budgetId = 7;

        $search = collect($agent->tools())->first(fn ($t) => $t instanceof SearchExpenses);
        assert($search instanceof SearchExpenses);

        expect($search)->toBeInstanceOf(SearchExpenses::class)
            ->and($search->budgetId)->toBe(7);
    });

    it('includes an AddExpense tool wired to the correct budgetId', function () {
        $agent = new BudgetAssistant;
        $agent->budgetId = 7;

        $add = collect($agent->tools())->first(fn ($t) => $t instanceof AddExpense);
        assert($add instanceof AddExpense);

        expect($add)->toBeInstanceOf(AddExpense::class)
            ->and($add->budgetId)->toBe(7);
    });

    it('returns an empty messages iterable', function () {
        $agent = new BudgetAssistant;

        expect($agent->messages())->toBeEmpty();
    });
});
