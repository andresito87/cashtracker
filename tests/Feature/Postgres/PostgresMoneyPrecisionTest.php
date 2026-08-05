<?php

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\QueryException;

uses()->group('pgsql');

describe('PostgreSQL money precision and constraints', function () {
    it('rejects numeric overflow at the column level (99999999.99 + 0.01)', function () {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create(['amount' => 99999999.99]);

        $thrown = false;
        try {
            Expense::create([
                'budget_id' => $budget->id,
                'name' => 'overflow',
                'amount' => '100000000.00',
                'category' => 'food',
            ]);
        } catch (QueryException $e) {
            $thrown = true;
            expect($e->getMessage())->toContain('numeric field overflow');
        }

        expect($thrown)->toBeTrue('Expected numeric overflow rejection, but insert succeeded.');
    });

    it('rejects a negative amount at the CHECK constraint level', function () {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);

        $thrown = false;
        try {
            Expense::create([
                'budget_id' => $budget->id,
                'name' => 'negative',
                'amount' => -1.00,
                'category' => 'food',
            ]);
        } catch (QueryException $e) {
            $thrown = true;
            expect($e->getMessage())->toContain('check constraint');
        }

        expect($thrown)->toBeTrue('Expected CHECK constraint rejection, but insert succeeded.');
    });

    it('accepts the upper-bound cap 99999999.99 as canonical decimal', function () {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create(['amount' => 99999999.99]);

        $expense = Expense::factory()->for($budget)->create([
            'name' => 'cap',
            'amount' => '99999999.99',
            'category' => 'food',
        ]);

        expect($expense->amount)->toBe('99999999.99');
    });

    it('accepts the minimum 0.01', function () {
        $user = User::factory()->create();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);

        $expense = Expense::factory()->for($budget)->create([
            'name' => 'min',
            'amount' => '0.01',
            'category' => 'food',
        ]);

        expect($expense->amount)->toBe('0.01');
    });
});
