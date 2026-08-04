<?php

use App\Ai\Agents\BudgetAssistant;
use App\Models\Budget;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StreamableAgentResponse;

/**
 * The regular BudgetChatTest suite runs with CSRF disabled (APP_ENV=testing
 * makes the framework skip token checks). These tests force the real
 * production environment so `PreventRequestForgery` actually enforces CSRF,
 * locking the contract the chat transport relies on.
 */
function fakeChatAssistant(): BudgetAssistant
{
    return new class extends BudgetAssistant
    {
        public function stream(
            mixed $prompt,
            array $attachments = [],
            mixed $provider = null,
            ?string $model = null,
            ?int $timeout = null
        ): StreamableAgentResponse {
            return new StreamableAgentResponse('fake-id', function () {
                yield from [];
            })->usingVercelDataProtocol();
        }
    };
}

function withForcedCsrf(callable $callback): void
{
    app()->instance('env', 'production');

    try {
        $callback();
    } finally {
        app()->instance('env', 'testing');
    }
}

describe('BudgetChatController — real CSRF enforcement', function () {
    it('returns 419 for a request without a CSRF token', function () {
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create();

        withForcedCsrf(function () use ($budget) {
            $this->post(route('budgets.chat', $budget), [
                'messages' => [['content' => 'hola']],
            ])->assertStatus(419);
        });
    });

    it('accepts the request when a valid CSRF token is sent', function () {
        $user = actingAsSubscribedUser();
        $budget = Budget::factory()->for($user)->create();
        $token = Str::random(40);

        $this->withSession(['_token' => $token]);
        $this->instance(BudgetAssistant::class, fakeChatAssistant());

        withForcedCsrf(function () use ($budget, $token) {
            $this->post(route('budgets.chat', $budget), [
                'messages' => [['content' => 'hola']],
            ], ['X-CSRF-TOKEN' => $token])->assertOk();
        });
    });
});
