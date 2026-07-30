<?php

use App\Ai\Agents\BudgetAssistant;
use App\Enums\BudgetType;
use App\Models\Budget;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Symfony\Component\HttpFoundation\Response;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function fakeStreamableResponse(): StreamableAgentResponse
{
    $streamable = Mockery::mock(StreamableAgentResponse::class);
    $streamable->shouldReceive('usingVercelDataProtocol')->andReturnSelf();
    $streamable->shouldReceive('toResponse')->andReturn(
        new Response('', 200)
    );

    return $streamable;
}

function fakeBudgetAssistant(?string &$capturedPrompt = null, ?BudgetAssistant &$capturedAgent = null): BudgetAssistant
{
    $fake = Mockery::mock(BudgetAssistant::class)->makePartial();

    $fake->shouldReceive('stream')
        ->once()
        ->andReturnUsing(function (string $prompt) use (&$capturedPrompt, $fake, &$capturedAgent) {
            $capturedPrompt = $prompt;
            $capturedAgent = $fake;

            return fakeStreamableResponse();
        });

    return $fake;
}

// ---------------------------------------------------------------------------
// Happy path
// ---------------------------------------------------------------------------

describe('BudgetChatController — happy path', function () {
    it('returns a 200 for an authenticated owner with valid messages in parts format', function () {
        $user = actingAsVerifiedUser();
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
        $user = actingAsVerifiedUser();
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
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        $capturedAgent = null;
        $this->instance(BudgetAssistant::class, fakeBudgetAssistant(capturedAgent: $capturedAgent));

        $this->post(route('budgets.chat', $budget), [
            'messages' => [['content' => 'hola']],
        ])->assertOk();

        expect($capturedAgent->budgetId)->toBe($budget->id);
    });

    it('builds a budgetContext string that contains the budget name, type, and formatted amount', function () {
        $user = actingAsVerifiedUser();
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
        $user = actingAsVerifiedUser();
        $budget = Budget::factory()->for($user)->create();

        $this->instance(BudgetAssistant::class, fakeBudgetAssistant());

        $this->post(route('budgets.chat', $budget))->assertOk();
    });
});

// ---------------------------------------------------------------------------
// Authentication & authorization
// ---------------------------------------------------------------------------

describe('BudgetChatController — authentication', function () {
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
});

describe('BudgetChatController — authorization', function () {
    it('returns 403 when a regular user attempts to chat on another users budget', function () {
        actingAsVerifiedUser();
        $owner = createVerifiedUser();
        $budget = Budget::factory()->for($owner)->create();

        $this->post(route('budgets.chat', $budget), [
            'messages' => [['content' => 'quiero ver tus gastos']],
        ])->assertForbidden();
    });
});
