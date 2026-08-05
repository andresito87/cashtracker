<?php

namespace App\Services;

use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use App\Support\MoneyAmount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Atomic, concurrency-safe expense mutations.
 *
 * Every create, update, bulk insert, and soft delete runs inside a single
 * {@see DB::transaction()} that locks the owning budget row with
 * {@see lockForUpdate()}, recomputes the active expense sum, and writes rows in
 * the same transaction. The locked service check is authoritative; FormRequest
 * validation only shapes UX.
 */
class ExpenseService
{
    /**
     * Create a single expense under a locked budget row.
     *
     * @param  array{name: string, amount: string|int|float, category: ExpenseCategory|string|null}  $attributes
     *
     * @throws ExpenseOverspendException
     */
    public function create(Budget $budget, array $attributes): Expense
    {
        return $this->withinLockedBudget($budget, $attributes['amount'], function (Budget $locked) use ($attributes) {
            $money = MoneyAmount::fromString((string) $attributes['amount']);

            return $locked->expenses()->create([
                'name' => $attributes['name'],
                'amount' => $money->canonical(),
                'category' => $this->resolveCategory($attributes['category'] ?? null),
            ]);
        });
    }

    /**
     * Update an existing expense under a locked budget row.
     *
     * @param  array{name: string, amount: string|int|float, category: ExpenseCategory|string|null}  $attributes
     *
     * @throws ExpenseOverspendException
     */
    public function update(Expense $expense, array $attributes): Expense
    {
        $money = MoneyAmount::fromString((string) $attributes['amount']);

        DB::transaction(function () use ($expense, $money, $attributes) {
            $budget = $expense->budget()->lockForUpdate()->firstOrFail();
            $this->assertWithinBudget($budget, $money, excluding: $expense->id);

            $expense->update([
                'name' => $attributes['name'],
                'amount' => $money->canonical(),
                'category' => $this->resolveCategory($attributes['category'] ?? null),
            ]);
        }, attempts: 3);

        return $expense->fresh();
    }

    /**
     * Bulk-insert expenses under one locked budget row and one transaction.
     *
     * @param  array<int, array{name: string, amount: string|int|float, category: ExpenseCategory|string|null}>  $rows
     * @return Collection<int, Expense>
     *
     * @throws ExpenseOverspendException
     */
    public function createMany(Budget $budget, array $rows): Collection
    {
        $parsed = array_map(fn (array $row) => [
            'name' => $row['name'],
            'money' => MoneyAmount::fromString((string) $row['amount']),
            'category' => $this->resolveCategory($row['category'] ?? null),
        ], $rows);

        $addedCents = array_reduce(
            $parsed,
            fn (int $carry, array $row) => $carry + $row['money']->cents(),
            0,
        );

        return DB::transaction(function () use ($budget, $parsed, $addedCents) {
            $locked = Budget::whereKey($budget->getKey())->lockForUpdate()->firstOrFail();
            $spentCents = $this->activeSpentCents($locked);
            $budgetCents = MoneyAmount::parseCents((string) $locked->amount);

            if ($spentCents + $addedCents > $budgetCents) {
                throw new ExpenseOverspendException('The expense batch would exceed the remaining budget balance.');
            }

            $created = new Collection;
            foreach ($parsed as $row) {
                $created->push($locked->expenses()->create([
                    'name' => $row['name'],
                    'amount' => $row['money']->canonical(),
                    'category' => $row['category'],
                ]));
            }

            return $created;
        }, attempts: 3);
    }

    /**
     * Soft delete an expense through the same locked boundary.
     */
    public function delete(Expense $expense): void
    {
        DB::transaction(function () use ($expense) {
            $expense->budget()->lockForUpdate()->firstOrFail();
            $expense->delete();
        }, attempts: 3);
    }

    /**
     * Run a create inside a locked transaction.
     *
     * @param  callable(Budget): Expense  $callback
     *
     * @throws ExpenseOverspendException
     */
    private function withinLockedBudget(Budget $budget, string|int|float $amount, callable $callback): Expense
    {
        $money = MoneyAmount::fromString((string) $amount);

        return DB::transaction(function () use ($budget, $money, $callback) {
            $locked = Budget::whereKey($budget->getKey())->lockForUpdate()->firstOrFail();
            $this->assertWithinBudget($locked, $money);

            return $callback($locked);
        }, attempts: 3);
    }

    /**
     * Assert the money amount fits in the remaining budget, optionally excluding
     * an expense being updated.
     *
     * @throws ExpenseOverspendException
     */
    private function assertWithinBudget(Budget $budget, MoneyAmount $money, ?int $excluding = null): void
    {
        $query = $budget->expenses();

        if ($excluding) {
            $query->where('id', '!=', $excluding);
        }

        $spentCents = $this->sumToCents($query->sum('amount'));
        $budgetCents = MoneyAmount::parseCents((string) $budget->amount);

        if ($spentCents + $money->cents() > $budgetCents) {
            throw new ExpenseOverspendException('The expense amount would exceed the remaining budget balance.');
        }
    }

    private function activeSpentCents(Budget $budget): int
    {
        return $this->sumToCents($budget->expenses()->sum('amount'));
    }

    private function sumToCents(string|float|int $sum): int
    {
        return MoneyAmount::parseCents(number_format((float) $sum, 2, '.', ''));
    }

    private function resolveCategory(ExpenseCategory|string|null $category): ExpenseCategory
    {
        if ($category instanceof ExpenseCategory) {
            return $category;
        }

        if (is_string($category) && $category !== '') {
            return ExpenseCategory::tryFrom($category) ?? ExpenseCategory::Other;
        }

        return ExpenseCategory::Other;
    }
}
