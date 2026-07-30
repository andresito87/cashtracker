<?php

use App\Ai\Tools\AddExpense;
use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makeRequest(array $args): Request
{
    return new Request($args);
}

function addExpenseTool(int $budgetId): AddExpense
{
    return new AddExpense(budgetId: $budgetId);
}

// ---------------------------------------------------------------------------
// Happy path
// ---------------------------------------------------------------------------

describe('AddExpense — successful expense creation', function () {
    it('creates an expense with a valid name, amount, and known category', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 500]);

        $this->actingAs($user);

        $tool = addExpenseTool($budget->id);
        $result = $tool->handle(makeRequest([
            'name' => 'Gasolina',
            'amount' => 45.50,
            'category' => 'transportation',
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_CREATED]');

        $this->assertDatabaseHas('expenses', [
            'budget_id' => $budget->id,
            'name' => 'Gasolina',
            'category' => 'transportation',
        ]);
    });

    it('defaults to category "other" when an unknown category is supplied', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 500]);

        $this->actingAs($user);

        $tool = addExpenseTool($budget->id);
        $tool->handle(makeRequest([
            'name' => 'Compra random',
            'amount' => 20,
            'category' => 'nonexistent_category',
        ]));

        $this->assertDatabaseHas('expenses', [
            'budget_id' => $budget->id,
            'name' => 'Compra random',
            'category' => ExpenseCategory::Other->value,
        ]);
    });

    it('defaults to category "other" when category is omitted', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 500]);

        $this->actingAs($user);

        $tool = addExpenseTool($budget->id);
        $tool->handle(makeRequest(['name' => 'Almuerzo', 'amount' => 15]));

        $this->assertDatabaseHas('expenses', [
            'budget_id' => $budget->id,
            'name' => 'Almuerzo',
            'category' => ExpenseCategory::Other->value,
        ]);
    });
});

// ---------------------------------------------------------------------------
// Name validation
// ---------------------------------------------------------------------------

describe('AddExpense — name validation', function () {
    it('rejects an empty name', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 200]);

        $this->actingAs($user);

        $result = addExpenseTool($budget->id)->handle(makeRequest([
            'name' => '',
            'amount' => 30,
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_ERROR]');
        $this->assertDatabaseCount('expenses', 0);
    });

    it('rejects a name shorter than two characters', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 200]);

        $this->actingAs($user);

        $result = addExpenseTool($budget->id)->handle(makeRequest([
            'name' => 'A',
            'amount' => 10,
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_ERROR]');
    });

    it('rejects generic placeholder names', function (string $invalidName) {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 500]);

        $this->actingAs($user);

        $result = addExpenseTool($budget->id)->handle(makeRequest([
            'name' => $invalidName,
            'amount' => 10,
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_ERROR]');
        $this->assertDatabaseCount('expenses', 0);
    })->with(['?', '??', '???', 'gasto', 'sin nombre', 'desconocido', 'unnamed', 'none', 'null', 'undefined', 'varios']);
});

// ---------------------------------------------------------------------------
// Amount validation
// ---------------------------------------------------------------------------

describe('AddExpense — amount validation', function () {
    it('rejects a non-numeric amount', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 200]);

        $this->actingAs($user);

        $result = addExpenseTool($budget->id)->handle(makeRequest([
            'name' => 'Taxi',
            'amount' => 'not-a-number',
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_ERROR]');
    });

    it('rejects a zero amount', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 200]);

        $this->actingAs($user);

        $result = addExpenseTool($budget->id)->handle(makeRequest([
            'name' => 'Taxi',
            'amount' => 0,
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_ERROR]');
    });

    it('rejects a negative amount', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 200]);

        $this->actingAs($user);

        $result = addExpenseTool($budget->id)->handle(makeRequest([
            'name' => 'Taxi',
            'amount' => -50,
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_ERROR]');
    });
});

// ---------------------------------------------------------------------------
// Business rules
// ---------------------------------------------------------------------------

describe('AddExpense — business rules', function () {
    it('rejects an expense that exceeds the remaining budget balance', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        Expense::factory()->for($budget)->create(['amount' => 80]);

        $this->actingAs($user);

        $result = addExpenseTool($budget->id)->handle(makeRequest([
            'name' => 'Vuelo a Ibiza',
            'amount' => 30, // 80 + 30 > 100
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_ERROR]');
        $this->assertDatabaseCount('expenses', 1); // original only
    });

    it('allows an expense that exactly matches the remaining balance', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        Expense::factory()->for($budget)->create(['amount' => 60]);

        $this->actingAs($user);

        $result = addExpenseTool($budget->id)->handle(makeRequest([
            'name' => 'Supermercado',
            'amount' => 40,
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_CREATED]');
    });
});

// ---------------------------------------------------------------------------
// Authorization
// ---------------------------------------------------------------------------

describe('AddExpense — authorization', function () {
    it('rejects when the budget belongs to another user', function () {
        $owner = createVerifiedUser();
        $intruder = createVerifiedUser();
        $budget = Budget::factory()->for($owner)->create(['amount' => 500]);

        $this->actingAs($intruder);

        $result = addExpenseTool($budget->id)->handle(makeRequest([
            'name' => 'Gasto ajeno',
            'amount' => 50,
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_ERROR]');
        $this->assertDatabaseCount('expenses', 0);
    });

    it('rejects when the budget does not exist', function () {
        $user = createVerifiedUser();
        $this->actingAs($user);

        $result = addExpenseTool(99999)->handle(makeRequest([
            'name' => 'Phantom',
            'amount' => 10,
        ]));

        expect((string) $result)->toStartWith('[EXPENSE_ERROR]');
        $this->assertDatabaseCount('expenses', 0);
    });
});

// ---------------------------------------------------------------------------
// Tool metadata
// ---------------------------------------------------------------------------

describe('AddExpense — tool metadata', function () {
    it('returns a non-empty description', function () {
        $tool = new AddExpense(budgetId: 1);

        expect((string) $tool->description())->toBeString()->not->toBeEmpty();
    });

    it('schema contains the required name and amount fields and the optional category', function () {
        $tool = new AddExpense(budgetId: 1);
        $schema = Mockery::mock(JsonSchema::class);
        $schema->shouldReceive('string')->andReturnSelf();
        $schema->shouldReceive('number')->andReturnSelf();
        $schema->shouldReceive('description')->andReturnSelf();
        $schema->shouldReceive('required')->andReturnSelf();

        $definition = $tool->schema($schema);

        expect($definition)->toHaveKey('name')
            ->toHaveKey('amount')
            ->toHaveKey('category');
    });
});
