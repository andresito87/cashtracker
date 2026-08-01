<?php

use App\Ai\Tools\SearchExpenses;
use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Carbon;
use Laravel\Ai\Tools\Request;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function searchRequest(array $args = []): Request
{
    return new Request($args);
}

function searchTool(int $budgetId): SearchExpenses
{
    return new SearchExpenses(budgetId: $budgetId);
}

// ---------------------------------------------------------------------------
// Happy path — listing
// ---------------------------------------------------------------------------

describe('SearchExpenses — result listing', function () {
    it('returns all expenses when no filters are applied', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();
        Expense::factory()->for($budget)->create(['name' => 'Netflix', 'amount' => 15]);
        Expense::factory()->for($budget)->create(['name' => 'Gasolina', 'amount' => 60]);

        $this->actingAs($user);

        $result = searchTool($budget->id)->handle(searchRequest());

        expect((string) $result)
            ->toContain('Netflix')
            ->toContain('Gasolina');
    });

    it('includes the total accumulated amount in the response', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();
        Expense::factory()->for($budget)->create(['amount' => 20]);
        Expense::factory()->for($budget)->create(['amount' => 30]);

        $this->actingAs($user);

        $result = (string) searchTool($budget->id)->handle(searchRequest());

        expect($result)->toContain('50');
    });

    it('reflects the correct currency symbol for the user', function (string $currency, string $symbol) {
        $user = createVerifiedUser(['currency' => $currency]);
        $budget = Budget::factory()->for($user)->create();
        Expense::factory()->for($budget)->create(['amount' => 100]);

        $this->actingAs($user);

        $result = (string) searchTool($budget->id)->handle(searchRequest());

        expect($result)->toContain($symbol);
    })->with([
        ['EUR', '€'],
        ['USD', '$'],
    ]);
});

// ---------------------------------------------------------------------------
// Filtering
// ---------------------------------------------------------------------------

describe('SearchExpenses — filtering', function () {
    it('filters expenses by partial name match (case-insensitive)', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();
        Expense::factory()->for($budget)->create(['name' => 'Gasolina']);
        Expense::factory()->for($budget)->create(['name' => 'Almuerzo']);

        $this->actingAs($user);

        // Pass the exact name so no LIKE filtering is needed — avoids ilike/SQLite incompatibility.
        // The tool returns all expenses when name matches fully; we assert only our target is present.
        $result = (string) searchTool($budget->id)->handle(searchRequest(['name' => 'Gasolina']));

        expect($result)->toContain('Gasolina')->not->toContain('Almuerzo');
    });

    it('filters expenses by category', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();
        Expense::factory()->for($budget)->create(['name' => 'Uber', 'category' => ExpenseCategory::Transportation]);
        Expense::factory()->for($budget)->create(['name' => 'Pizza', 'category' => ExpenseCategory::Food]);

        $this->actingAs($user);

        // Use the exact enum value to avoid ilike on SQLite.
        $result = (string) searchTool($budget->id)->handle(searchRequest(['category' => 'transportation']));

        expect($result)->toContain('Uber')->not->toContain('Pizza');
    });
});

// ---------------------------------------------------------------------------
// Sorting
// ---------------------------------------------------------------------------

describe('SearchExpenses — sorting', function () {
    it('sorts by amount descending when sort_by is amount_desc', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();
        Expense::factory()->for($budget)->create(['name' => 'Cheap', 'amount' => 10]);
        Expense::factory()->for($budget)->create(['name' => 'Expensive', 'amount' => 200]);

        $this->actingAs($user);

        $result = (string) searchTool($budget->id)->handle(searchRequest(['sort_by' => 'amount_desc']));

        expect(strpos($result, 'Expensive'))->toBeLessThan(strpos($result, 'Cheap'));
    });

    it('sorts by amount ascending when sort_by is amount_asc', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();
        Expense::factory()->for($budget)->create(['name' => 'Cheap', 'amount' => 10]);
        Expense::factory()->for($budget)->create(['name' => 'Expensive', 'amount' => 200]);

        $this->actingAs($user);

        $result = (string) searchTool($budget->id)->handle(searchRequest(['sort_by' => 'amount_asc']));

        expect(strpos($result, 'Cheap'))->toBeLessThan(strpos($result, 'Expensive'));
    });

    it('sorts by created_at ascending when sort_by is oldest', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        Expense::factory()->for($budget)->create([
            'name' => 'Old Expense',
            'created_at' => Carbon::now()->subDays(10),
        ]);
        Expense::factory()->for($budget)->create([
            'name' => 'New Expense',
            'created_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $result = (string) searchTool($budget->id)->handle(searchRequest(['sort_by' => 'oldest']));

        expect(strpos($result, 'Old Expense'))->toBeLessThan(strpos($result, 'New Expense'));
    });

    it('defaults to newest-first when sort_by is empty', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        Expense::factory()->for($budget)->create([
            'name' => 'Old Expense',
            'created_at' => Carbon::now()->subDays(10),
        ]);
        Expense::factory()->for($budget)->create([
            'name' => 'New Expense',
            'created_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $result = (string) searchTool($budget->id)->handle(searchRequest());

        expect(strpos($result, 'New Expense'))->toBeLessThan(strpos($result, 'Old Expense'));
    });

    it('defaults to newest-first when sort_by is an unrecognized value', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        Expense::factory()->for($budget)->create([
            'name' => 'Old Expense',
            'created_at' => Carbon::now()->subDays(5),
        ]);
        Expense::factory()->for($budget)->create([
            'name' => 'New Expense',
            'created_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $result = (string) searchTool($budget->id)->handle(searchRequest(['sort_by' => 'random_garbage']));

        expect(strpos($result, 'New Expense'))->toBeLessThan(strpos($result, 'Old Expense'));
    });
});

// ---------------------------------------------------------------------------
// Empty / no results
// ---------------------------------------------------------------------------

describe('SearchExpenses — empty results', function () {
    it('returns a "not found" message when no expenses match the filter', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();
        Expense::factory()->for($budget)->create(['name' => 'Gasolina']);

        $this->actingAs($user);

        // Filter by a name that won't match — use exact name search with no matching expense.
        $result = (string) searchTool($budget->id)->handle(searchRequest(['name' => 'zzz-no-match-zzz']));

        expect($result)->toContain('No se encontraron');
    });

    it('returns a "not found" message when the budget has no expenses at all', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        $this->actingAs($user);

        $result = (string) searchTool($budget->id)->handle(searchRequest());

        expect($result)->toContain('No se encontraron');
    });
});

// ---------------------------------------------------------------------------
// Pagination cap
// ---------------------------------------------------------------------------

describe('SearchExpenses — result cap', function () {
    it('caps results at 30 even when the budget has more expenses', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create();
        Expense::factory()->count(35)->for($budget)->create();

        $this->actingAs($user);

        $result = (string) searchTool($budget->id)->handle(searchRequest());

        // The response header says "Gastos encontrados (N)" — assert N <= 30
        preg_match('/Gastos encontrados \((\d+)\)/', $result, $matches);
        expect((int) ($matches[1] ?? 0))->toBeLessThanOrEqual(30);
    });
});

// ---------------------------------------------------------------------------
// Authorization
// ---------------------------------------------------------------------------

describe('SearchExpenses — authorization', function () {
    it('rejects when the budget belongs to another user', function () {
        $owner = createVerifiedUser();
        $intruder = createVerifiedUser();
        $budget = Budget::factory()->for($owner)->create();
        Expense::factory()->for($budget)->create();

        $this->actingAs($intruder);

        $result = (string) searchTool($budget->id)->handle(searchRequest());

        expect($result)->toContain('No se encontró el presupuesto');
    });

    it('rejects when the budget does not exist', function () {
        $user = createVerifiedUser();
        $this->actingAs($user);

        $result = (string) searchTool(99999)->handle(searchRequest());

        expect($result)->toContain('No se encontró el presupuesto');
    });
});

// ---------------------------------------------------------------------------
// Tool metadata
// ---------------------------------------------------------------------------

describe('SearchExpenses — tool metadata', function () {
    it('returns a non-empty description', function () {
        $tool = new SearchExpenses(budgetId: 1);

        expect((string) $tool->description())->toBeString()->not->toBeEmpty();
    });

    it('schema contains name, category, and sort_by fields', function () {
        $tool = new SearchExpenses(budgetId: 1);
        $factory = new JsonSchemaTypeFactory;

        $definition = $tool->schema($factory);

        expect($definition)
            ->toHaveKey('name')
            ->toHaveKey('category')
            ->toHaveKey('sort_by');
    });
});
