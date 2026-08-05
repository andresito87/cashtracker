<?php

use App\Ai\Agents\TicketScanner;
use App\Ai\Tools\AddExpense;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Approvals\Decisions;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Laravel\Ai\Tools\Request;

function fakeScannerResponseFor(array $items, string $store = 'Mercado', string $category = 'food'): array
{
    return ['store' => $store, 'category' => $category, 'items' => $items];
}

function stubTicketScanner(array $scannerResult): void
{
    $response = new StructuredAgentResponse('fake', $scannerResult, json_encode($scannerResult), new Usage, new Meta);

    $fakeScanner = new class($response) extends TicketScanner
    {
        public function __construct(public StructuredAgentResponse $cResponse)
        {
            parent::__construct();
        }

        public function prompt(Decisions|string $prompt, array $attachments = [], Lab|array|string|null $provider = null, ?string $model = null, ?int $timeout = null): StructuredAgentResponse
        {
            return $this->cResponse;
        }
    };

    app()->instance(TicketScanner::class, $fakeScanner);
}

describe('Stored-value feedback across paths', function () {
    it('rounds AI tool amounts to two decimals and reports the persisted amount', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 500]);
        $this->actingAs($user);

        $tool = new AddExpense(budgetId: $budget->id);
        $result = (string) $tool->handle(new Request([
            'name' => 'Gasto con decimales',
            'amount' => '45.501',
            'category' => 'transportation',
        ]));

        expect($result)->toStartWith('[EXPENSE_CREATED]')
            ->and($result)->toContain('45.50');

        $this->assertDatabaseHas('expenses', [
            'budget_id' => $budget->id,
            'amount' => '45.50',
        ]);
    });

    it('form path persists the canonical two-decimal amount and reports the stored value', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 500]);

        $this->post(route('budgets.expenses.store', $budget), [
            'name' => 'Form Two Decimals',
            'amount' => '12.34',
            'category' => 'food',
        ])->assertRedirect();

        $expense = Expense::where('budget_id', $budget->id)->firstOrFail();
        assert($expense instanceof Expense);
        expect($expense->amount)->toBe('12.34');
    });

    it('ticket scan persists the rounded per-item amounts and reports the stored total', function () {
        Storage::fake('local');
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);

        stubTicketScanner(fakeScannerResponseFor([
            ['name' => 'A', 'amount' => 0.333],
            ['name' => 'B', 'amount' => 0.333],
        ]));

        $this->postJson(route('budgets.scan-ticket', $budget), [
            'image' => UploadedFile::fake()->image('t.jpg'),
        ])->assertOk()
            ->assertJsonPath('success', true);

        $persisted = Expense::where('budget_id', $budget->id)->orderBy('id')->get();
        expect($persisted->pluck('amount')->all())->toBe(['0.33', '0.33']);
    });

    it('form overspend preserves the existing validation error wording', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        Expense::factory()->for($budget)->create(['amount' => 70]);

        $this->post(route('budgets.expenses.store', $budget), [
            'name' => 'Too Much',
            'amount' => '40',
            'category' => 'food',
        ])->assertSessionHasErrors(['amount' => __('messages.validation_amount_exceeds_balance')]);
    });

    it('AI tool overspend returns [EXPENSE_ERROR] and persists nothing', function () {
        $user = createVerifiedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 100]);
        Expense::factory()->for($budget)->create(['amount' => 80]);
        $this->actingAs($user);

        $tool = new AddExpense(budgetId: $budget->id);
        $result = (string) $tool->handle(new Request([
            'name' => 'Vuelo',
            'amount' => 30,
            'category' => 'transportation',
        ]));

        expect($result)->toStartWith('[EXPENSE_ERROR]')
            ->and(Expense::where('budget_id', $budget->id)->count())->toBe(1);
    });

    it('ticket scan overspend persists no rows and reports insufficient budget', function () {
        Storage::fake('local');
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create(['amount' => 50]);
        Expense::factory()->for($budget)->create(['amount' => 40]);

        stubTicketScanner(fakeScannerResponseFor([
            ['name' => 'Cena', 'amount' => 15],
        ]));

        $this->postJson(route('budgets.scan-ticket', $budget), [
            'image' => UploadedFile::fake()->image('d.jpg'),
        ])->assertStatus(422)
            ->assertJsonPath('success', false);

        expect(Expense::where('budget_id', $budget->id)->count())->toBe(1);
    });
});
