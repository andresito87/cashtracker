<?php

use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use App\Services\ExpenseOverspendException;
use App\Services\ExpenseService;
use App\Support\InvalidMoney;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

function expenseService(): ExpenseService
{
    return app(ExpenseService::class);
}

describe('ExpenseService::create', function () {
    it('creates an expense inside a transaction and stores the canonical rounded amount', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);

        $expense = expenseService()->create($budget, [
            'name' => 'Coffee',
            'amount' => '12.34',
            'category' => ExpenseCategory::Food,
        ]);

        expect($expense)->toBeInstanceOf(Expense::class)
            ->and($expense->amount)->toBe('12.34')
            ->and($expense->budget_id)->toBe($budget->id);

        $this->assertDatabaseHas('expenses', [
            'budget_id' => $budget->id,
            'name' => 'Coffee',
            'amount' => '12.34',
        ]);
    });

    it('rounds three-decimal input to two decimals before persisting', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);

        $expense = expenseService()->create($budget, [
            'name' => 'AI Item',
            'amount' => '45.501',
            'category' => ExpenseCategory::Other,
        ]);

        expect($expense->amount)->toBe('45.50');
    });

    it('rejects an overspending create without persisting any row', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 50]);
        Expense::factory()->for($budget)->create(['amount' => 40]);

        expect(fn () => expenseService()->create($budget, [
            'name' => 'Too Much',
            'amount' => '20.00',
            'category' => ExpenseCategory::Other,
        ]))->toThrow(ExpenseOverspendException::class)
            ->and(Expense::where('budget_id', $budget->id)->count())->toBe(1);

        // Only the seeded 40.00 expense exists.
    });

    it('allows a create that exactly matches the remaining balance', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        Expense::factory()->for($budget)->create(['amount' => 70]);

        $expense = expenseService()->create($budget, [
            'name' => 'Exact',
            'amount' => '30.00',
            'category' => ExpenseCategory::Other,
        ]);

        expect($expense->amount)->toBe('30.00');
    });

    it('excludes soft-deleted expenses when recomputing the active sum', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        $deleted = Expense::factory()->for($budget)->create(['amount' => 90]);
        $deleted->delete();

        $expense = expenseService()->create($budget, [
            'name' => 'Reused Balance',
            'amount' => '90.00',
            'category' => ExpenseCategory::Other,
        ]);

        expect($expense->amount)->toBe('90.00');
    });

    it('rejects non-canonical input (scientific notation) before any write', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);

        expect(fn () => expenseService()->create($budget, [
            'name' => 'Bad',
            'amount' => '1e3',
            'category' => ExpenseCategory::Other,
        ]))->toThrow(InvalidMoney::class)
            ->and(Expense::where('budget_id', $budget->id)->count())->toBe(0);

    });
});

describe('ExpenseService::update', function () {
    it('acquires the budget lock inside the update transaction', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        $expense = Expense::factory()->for($budget)->create(['amount' => 10]);
        $outerTransactionLevel = DB::transactionLevel();
        $budgetQueryTransactionLevels = [];

        DB::listen(function (QueryExecuted $query) use (&$budgetQueryTransactionLevels): void {
            $sql = strtolower($query->sql);

            if (str_contains($sql, 'from "budgets"') && str_contains($sql, 'limit 1')) {
                $budgetQueryTransactionLevels[] = $query->connection->transactionLevel();
            }
        });

        expenseService()->update($expense, [
            'name' => 'Updated',
            'amount' => '25.99',
            'category' => ExpenseCategory::Entertainment,
        ]);

        expect($budgetQueryTransactionLevels)->not->toBeEmpty()
            ->and(max($budgetQueryTransactionLevels))->toBeGreaterThan($outerTransactionLevel);
    });

    it('updates an expense and stores the canonical amount', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        $expense = Expense::factory()->for($budget)->create(['amount' => 10]);

        expenseService()->update($expense, [
            'name' => 'Updated',
            'amount' => '25.99',
            'category' => ExpenseCategory::Entertainment,
        ]);

        expect($expense->fresh()->amount)->toBe('25.99')
            ->and($expense->fresh()->name)->toBe('Updated');
    });

    it('rejects an update that would overspend the budget', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        Expense::factory()->for($budget)->create(['amount' => 40]);
        $expenseB = Expense::factory()->for($budget)->create(['amount' => 30]);

        // Available excluding B is 60; updating B to 70 must fail.
        expect(fn () => expenseService()->update($expenseB, [
            'name' => 'Big',
            'amount' => '70.00',
            'category' => ExpenseCategory::Other,
        ]))->toThrow(ExpenseOverspendException::class)
            ->and($expenseB->fresh()->amount)->toBe('30.00');

        // B unchanged.
    });
});

describe('ExpenseService::createMany', function () {
    it('inserts a bulk of expenses in one transaction at canonical amounts', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);

        $expenses = expenseService()->createMany($budget, [
            ['name' => 'Item A', 'amount' => '0.333', 'category' => ExpenseCategory::Food],
            ['name' => 'Item B', 'amount' => '0.333', 'category' => ExpenseCategory::Food],
            ['name' => 'Item C', 'amount' => '0.333', 'category' => ExpenseCategory::Food],
        ]);

        expect($expenses)->toHaveCount(3);
        $persisted = Expense::where('budget_id', $budget->id)->orderBy('id')->get();
        expect($persisted->pluck('amount')->all())->toBe(['0.33', '0.33', '0.33']);
    });

    it('rejects a bulk that would overspend without inserting any row', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 10]);
        Expense::factory()->for($budget)->create(['amount' => 5]);

        expect(fn () => expenseService()->createMany($budget, [
            ['name' => 'A', 'amount' => '20.00', 'category' => ExpenseCategory::Food],
            ['name' => 'B', 'amount' => '5.00', 'category' => ExpenseCategory::Food],
        ]))->toThrow(ExpenseOverspendException::class)
            ->and(Expense::where('budget_id', $budget->id)->count())->toBe(1);

        // Only the seeded expense exists; no partial insert.
    });
});

describe('ExpenseService::delete', function () {
    it('soft deletes the expense through the same locked boundary', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        $expense = Expense::factory()->for($budget)->create(['amount' => 30]);

        expenseService()->delete($expense);

        $this->assertSoftDeleted($expense);
    });

    it('frees balance after delete allowing a later create to use it', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        $expense = Expense::factory()->for($budget)->create(['amount' => 90]);

        expenseService()->delete($expense);

        $new = expenseService()->create($budget, [
            'name' => 'Reused',
            'amount' => '90.00',
            'category' => ExpenseCategory::Other,
        ]);

        expect($new->amount)->toBe('90.00');
    });
});
