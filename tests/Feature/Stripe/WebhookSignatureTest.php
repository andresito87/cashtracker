<?php

function signedWebhookHeader(string $payload, string $secret, ?int $timestamp = null): string
{
    $timestamp = $timestamp ?? time();
    $signature = hash_hmac('sha256', "$timestamp.$payload", $secret);

    return "t=$timestamp,v1=$signature";
}

/**
 * @throws JsonException
 */
function webhookPayload(array $overrides = []): string
{
    return json_encode(array_merge([
        'id' => 'evt_test_'.uniqid(),
        'type' => 'unknown.event.type',
        'data' => [],
    ], $overrides), JSON_THROW_ON_ERROR);
}

describe('Stripe webhook signature verification', function () {
    it('fails closed when the webhook secret is not configured', function () {
        config(['cashier.webhook.secret' => null]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->postJson('/stripe/webhook', json_decode(webhookPayload(), true, flags: JSON_THROW_ON_ERROR))
            ->assertStatus(403);
    });

    it('accepts a request signed with the configured secret', function () {
        $secret = 'whsec_test_valid';
        config(['cashier.webhook.secret' => $secret]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $payload = webhookPayload();
        $header = signedWebhookHeader($payload, $secret);

        $this->call(
            'POST',
            '/stripe/webhook',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_Stripe-Signature' => $header],
            content: $payload,
        )->assertOk();
    });

    it('rejects a request signed with the wrong secret', function () {
        $secret = 'whsec_test_valid';
        config(['cashier.webhook.secret' => $secret]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $payload = webhookPayload();
        $header = signedWebhookHeader($payload, 'whsec_test_wrong');

        $this->call(
            'POST',
            '/stripe/webhook',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_Stripe-Signature' => $header],
            content: $payload,
        )->assertStatus(403);
    });

    it('rejects a request whose signature timestamp is outside the tolerance zone', function () {
        $secret = 'whsec_test_valid';
        config(['cashier.webhook.secret' => $secret]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $payload = webhookPayload();
        $header = signedWebhookHeader($payload, $secret, time() - 3600);

        $this->call(
            'POST',
            '/stripe/webhook',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_Stripe-Signature' => $header],
            content: $payload,
        )->assertStatus(403);
    });
});
