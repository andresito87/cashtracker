<?php

use Illuminate\Support\Facades\Schema;

describe('database indexes for foreign key columns', function () {
    it('indexes the user_id column of the budgets table', function () {
        expect(Schema::hasIndex('budgets', ['user_id']))->toBeTrue();
    });

    it('indexes the budget_id column of the expenses table', function () {
        expect(Schema::hasIndex('expenses', ['budget_id']))->toBeTrue();
    });
});
