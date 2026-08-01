<?php

use App\Ai\Agents\TicketScanner;
use App\Enums\Currency;
use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Approvals\Decisions;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StructuredAgentResponse;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function fakeScannerResponse(array $items, string $store = 'Supermercado Dia', string $category = 'food'): array
{
    return [
        'store' => $store,
        'category' => $category,
        'items' => $items,
    ];
}

function mockTicketScanner(array $scannerResult): void
{
    $response = new StructuredAgentResponse(
        'fake-invocation',
        $scannerResult,
        json_encode($scannerResult),
        new Usage,
        new Meta
    );

    $fakeScanner = new class($response) extends TicketScanner
    {
        public function __construct(public StructuredAgentResponse $cResponse)
        {
            parent::__construct();
        }

        public function prompt(
            Decisions|string $prompt,
            array $attachments = [],
            Lab|array|string|null $provider = null,
            ?string $model = null,
            ?int $timeout = null
        ): StructuredAgentResponse {
            return $this->cResponse;
        }
    };

    app()->instance(TicketScanner::class, $fakeScanner);
}

// ---------------------------------------------------------------------------
// Tests
// ---------------------------------------------------------------------------

describe('TicketScanController — Happy Path', function () {
    beforeEach(function () {
        Storage::fake('local');
    });

    it('creates expenses and returns success response when items are valid and fit in remaining balance', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create([
            'amount' => 100,
        ]);

        mockTicketScanner(fakeScannerResponse([
            ['name' => 'Leche Entera', 'amount' => 3.50],
            ['name' => 'Pan Integral', 'amount' => 2.00],
        ]));

        $file = UploadedFile::fake()->image('ticket.jpg', 600, 800);

        $response = $this->postJson(route('budgets.scan-ticket', $budget), [
            'image' => $file,
        ]);

        $catLabel = ExpenseCategory::Food->label();
        $expectedMessage = "Se registraron 2 gastos del ticket:\n- Supermercado Dia - Leche Entera: €3.50 ($catLabel)\n- Supermercado Dia - Pan Integral: €2.00 ($catLabel)\nTotal: €5.50";

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', $expectedMessage);

        $this->assertDatabaseHas('expenses', [
            'budget_id' => $budget->id,
            'name' => 'Supermercado Dia - Leche Entera',
            'amount' => 3.50,
        ]);

        $this->assertDatabaseHas('expenses', [
            'budget_id' => $budget->id,
            'name' => 'Supermercado Dia - Pan Integral',
            'amount' => 2.00,
        ]);
    });

    it('uses the custom currency symbol of the user account in the response message', function () {
        $user = actingAsVerifiedUser(['currency' => Currency::USD]);
        $budget = Budget::factory()->for($user)->create([
            'amount' => 500,
        ]);

        mockTicketScanner(fakeScannerResponse([
            ['name' => 'Apple Watch Charger', 'amount' => 29.99],
        ], store: 'Apple Store', category: 'home'));

        $file = UploadedFile::fake()->image('ticket.png');

        $response = $this->postJson(route('budgets.scan-ticket', $budget), [
            'image' => $file,
        ]);

        $catLabel = ExpenseCategory::Home->label();
        $expectedMessage = "Se registraron 1 gastos del ticket:\n- Apple Store - Apple Watch Charger: $29.99 ($catLabel)\nTotal: $29.99";

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', $expectedMessage);
    });

    it('filters out $0.00 items from ticket and creates expenses only for non-zero items', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create([
            'amount' => 200,
        ]);

        mockTicketScanner(fakeScannerResponse([
            ['name' => 'Menu Ejecución', 'amount' => 18.50],
            ['name' => 'Agua con Gas (Incluida)', 'amount' => 0.00],
            ['name' => 'Postre Flan (Incluido)', 'amount' => 0.00],
        ], store: 'Restaurante Central'));

        $file = UploadedFile::fake()->image('menu_ticket.jpg');

        $response = $this->postJson(route('budgets.scan-ticket', $budget), [
            'image' => $file,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Verify only 1 expense was inserted in DB
        expect(Expense::where('budget_id', $budget->id)->count())->toBe(1);

        $this->assertDatabaseHas('expenses', [
            'budget_id' => $budget->id,
            'name' => 'Restaurante Central - Menu Ejecución',
            'amount' => 18.50,
        ]);
    });
});

describe('TicketScanController — Error & Edge Cases', function () {
    beforeEach(function () {
        Storage::fake('local');
    });

    it('returns HTTP 422 error and does NOT create expenses when ticket total exceeds remaining balance', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create([
            'amount' => 50.00,
        ]);

        // Create an existing expense of 40.00 so available balance is 10.00
        Expense::factory()->for($budget)->create([
            'amount' => 40.00,
        ]);

        // Scanner returns a ticket total of 15.00 (which exceeds 10.00 available balance)
        mockTicketScanner(fakeScannerResponse([
            ['name' => 'Cena Gourmet', 'amount' => 15.00],
        ]));

        $file = UploadedFile::fake()->image('dinner.jpg');

        $response = $this->postJson(route('budgets.scan-ticket', $budget), [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'El total del ticket (€15.00) excede el saldo disponible en este presupuesto (€10.00). No se registraron los gastos.');

        // Integrity check: total expenses in DB must remain exactly 1 (the initial 40.00)
        expect(Expense::where('budget_id', $budget->id)->count())->toBe(1);
    });

    it('returns HTTP 422 error when no items with amount > 0 are found in the ticket', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create([
            'amount' => 100,
        ]);

        mockTicketScanner(fakeScannerResponse([
            ['name' => 'Degustación Promocional', 'amount' => 0.00],
        ]));

        $file = UploadedFile::fake()->image('free_ticket.jpg');

        $response = $this->postJson(route('budgets.scan-ticket', $budget), [
            'image' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'No se encontraron productos con un importe mayor a 0 en el ticket.');

        expect(Expense::where('budget_id', $budget->id)->count())->toBe(0);
    });

    it('validates that image is required when scanning ticket', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        $this->from(route('budgets.show', $budget))
            ->post(route('budgets.scan-ticket', $budget))
            ->assertRedirect(route('budgets.show', $budget))
            ->assertSessionHasErrors(['image']);
    });

    it('forbids users from scanning tickets in budgets owned by other users', function () {
        $owner = actingAsVerifiedUser();
        $otherUser = actingAsVerifiedUser();
        $budget = Budget::factory()->for($owner)->create();

        $file = UploadedFile::fake()->image('ticket.jpg');

        // Acting as otherUser (not owner)
        $this->actingAs($otherUser)
            ->postJson(route('budgets.scan-ticket', $budget), [
                'image' => $file,
            ])
            ->assertForbidden();
    });

    it('rejects a PDF file with a validation error on the image field', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        $pdfFile = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        $this->from(route('budgets.show', $budget))
            ->post(route('budgets.scan-ticket', $budget), ['image' => $pdfFile])
            ->assertRedirect(route('budgets.show', $budget))
            ->assertSessionHasErrors(['image']);
    });

    it('rejects a plain text file with a validation error on the image field', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        $txtFile = UploadedFile::fake()->create('notes.txt', 1, 'text/plain');

        $this->from(route('budgets.show', $budget))
            ->post(route('budgets.scan-ticket', $budget), ['image' => $txtFile])
            ->assertRedirect(route('budgets.show', $budget))
            ->assertSessionHasErrors(['image']);
    });
});
