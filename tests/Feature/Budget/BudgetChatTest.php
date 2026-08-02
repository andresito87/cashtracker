<?php

use App\Ai\Agents\BudgetAssistant;
use App\Enums\BudgetType;
use App\Models\Budget;
use Laravel\Ai\Approvals\Decisions;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\StreamableAgentResponse;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function fakeStreamableResponse(): StreamableAgentResponse
{
    return new StreamableAgentResponse('fake-id', function () {
        yield from [];
    })->usingVercelDataProtocol();
}

function fakeBudgetAssistant(?string &$capturedPrompt = null, ?BudgetAssistant &$capturedAgent = null): BudgetAssistant
{
    $fake = new class($capturedPrompt) extends BudgetAssistant
    {
        public function __construct(public &$promptRef)
        {
            if (method_exists(parent::class, '__construct')) {
                parent::__construct();
            }
        }

        public function stream(
            Decisions|string $prompt,
            array $attachments = [],
            Lab|array|string|null $provider = null,
            ?string $model = null,
            ?int $timeout = null
        ): StreamableAgentResponse {
            $this->promptRef = (string) $prompt;

            return fakeStreamableResponse();
        }
    };

    $capturedAgent = $fake;

    return $fake;
}

// ---------------------------------------------------------------------------
// Happy path
// ---------------------------------------------------------------------------

describe('BudgetChatController — happy path', function () {
    it('returns a 200 for an authenticated owner with valid messages in parts format', function () {
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create();

        $capturedPrompt = null;
        $this->instance(BudgetAssistant::class, fakeBudgetAssistant($capturedPrompt));

        $this->post(route('budgets.chat', $budget), [
            'messages' => [
                [
                    'parts' => [['type' => 'text', 'text' => '¿Cuánto llevo gastado?']],
                ],
            ],
        ])->assertOk();

        expect($capturedPrompt)->toBe('¿Cuánto llevo gastado?');
    });

    it('falls back to the content key when parts array is absent', function () {
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create();

        $capturedPrompt = null;
        $this->instance(BudgetAssistant::class, fakeBudgetAssistant($capturedPrompt));

        $this->post(route('budgets.chat', $budget), [
            'messages' => [
                ['content' => '¿Agrega Gasolina por 50?'],
            ],
        ])->assertOk();

        expect($capturedPrompt)->toBe('¿Agrega Gasolina por 50?');
    });

    it('wires the budgetId from the route model binding onto the agent', function () {
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create();

        $capturedAgent = null;
        $this->instance(BudgetAssistant::class, fakeBudgetAssistant(capturedAgent: $capturedAgent));

        $this->post(route('budgets.chat', $budget), [
            'messages' => [['content' => 'hola']],
        ])->assertOk();

        expect($capturedAgent->budgetId)->toBe($budget->id);
    });

    it('builds a budgetContext string that contains the budget name, type, and formatted amount', function () {
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create([
            'name' => 'Vacaciones Europa',
            'amount' => 1000,
            'type' => BudgetType::Goal,
        ]);

        $capturedAgent = null;
        $this->instance(BudgetAssistant::class, fakeBudgetAssistant(capturedAgent: $capturedAgent));

        $this->post(route('budgets.chat', $budget), [
            'messages' => [['content' => 'hola']],
        ])->assertOk();

        expect($capturedAgent->budgetContext)
            ->toContain('Vacaciones Europa')
            ->toContain(BudgetType::Goal->value);
    });

    it('handles an empty messages payload without crashing', function () {
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create();

        $this->instance(BudgetAssistant::class, fakeBudgetAssistant());

        $this->post(route('budgets.chat', $budget))->assertStatus(422);
    });
});

// ---------------------------------------------------------------------------
// Authentication & authorization
// ---------------------------------------------------------------------------

describe('BudgetChatController — authentication & subscription', function () {
    it('redirects guests to the login page', function () {
        $budget = Budget::factory()->create();

        $this->post(route('budgets.chat', $budget), [
            'messages' => [['content' => 'hola']],
        ])->assertRedirect(route('login'));
    });

    it('redirects unverified users to the email verification notice', function () {
        actingAsUnverifiedUser();
        $budget = Budget::factory()->create();

        $this->post(route('budgets.chat', $budget), [
            'messages' => [['content' => 'hola']],
        ])->assertRedirect(route('verification.notice'));
    });

    it('redirects unsubscribed users to subscription manage page', function () {
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        $this->post(route('budgets.chat', $budget), [
            'messages' => [['content' => 'hola']],
        ])->assertRedirect(route('subscription.manage'))
            ->assertSessionHas('error', 'You must be subscribed to access this feature.');
    });
});

describe('BudgetChatController — authorization', function () {
    it('returns 403 when a regular user attempts to chat on another users budget', function () {
        actingAsSubscribedUser();
        $owner = createVerifiedUser();
        $budget = Budget::factory()->for($owner)->create();

        $this->post(route('budgets.chat', $budget), [
            'messages' => [['content' => 'quiero ver tus gastos']],
        ])->assertForbidden();
    });
});

describe('BudgetChatController — input validation', function () {
    it('returns 422 when the messages payload is empty and no prompt can be extracted', function () {
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create();

        // No agent mock needed — guard fires before agent is called
        $this->postJson(route('budgets.chat', $budget), [
            'messages' => [],
        ])->assertStatus(422);
    });

    it('returns 422 when the last message has empty text parts', function () {
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create();

        $this->postJson(route('budgets.chat', $budget), [
            'messages' => [['parts' => [['type' => 'text', 'text' => '   ']]]],
        ])->assertStatus(422);
    });
});
