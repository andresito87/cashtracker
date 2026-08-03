<?php

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\Notification;

it('renders the forgot password form on GET', function () {
    app()->setLocale('en');

    $this->get(route('password.request'))
        ->assertSuccessful()
        ->assertSee(__('messages.passwords.forgot.title'))
        ->assertSee(__('messages.passwords.forgot.submit'));
});

it('dispatches a reset link notification for a registered email and redirects back with a generic status', function () {
    Notification::fake();
    app()->setLocale('en');

    $user = createVerifiedUser(['email' => 'registered@example.com']);

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'registered@example.com'])
        ->assertRedirect(route('password.request'))
        ->assertSessionHas('status', __('messages.passwords.sent'));

    /** @noinspection PhpUnhandledExceptionInspection */
    Notification::assertSentTo($user, ResetPassword::class);
});

it('returns the same generic status for an unknown email without leaking the account existence', function () {
    Notification::fake();
    app()->setLocale('en');

    createVerifiedUser(['email' => 'known@example.com']);

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'known@example.com'])
        ->assertSessionHas('status', __('messages.passwords.sent'));

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'unknown@example.com'])
        ->assertSessionHas('status', __('messages.passwords.sent'));

    // No notification dispatched for the unknown email.
    /** @noinspection PhpUnhandledExceptionInspection */
    Notification::assertNotSentTo(
        new User(['email' => 'unknown@example.com']),
        ResetPassword::class
    );
});

it('rejects an empty email with a required validation error', function () {
    $this->post(route('password.email'), ['email' => ''])
        ->assertSessionHasErrors(['email']);
});

it('rejects an invalid email format with a validation error', function () {
    $this->post(route('password.email'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors(['email']);
});

it('preserves the old email value when validation fails', function () {
    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'kept-value'])
        ->assertSessionHasErrors(['email']);

    $this->get(route('password.request'))->assertSee('value="kept-value"', false);
});

it('rate limits the forgot password endpoint after 5 attempts in 1 minute', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('password.email'), ['email' => "unknown$i@example.com"])
            ->assertRedirect();
    }

    $this->post(route('password.email'), ['email' => 'unknown5@example.com'])
        ->assertTooManyRequests();
});

it('renders the reset-link email through the real mail channel', function () {
    config(['mail.default' => 'array']);

    createVerifiedUser(['email' => 'array-reset@example.com']);

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'array-reset@example.com'])
        ->assertRedirect(route('password.request'));

    $transport = app('mail.manager')->mailer()->getSymfonyTransport();
    assert($transport instanceof ArrayTransport);
    $messages = $transport->messages();
    expect($messages)->not->toBeEmpty();

    $rendered = str_replace("=\r\n", '', $messages->first()->toString());
    expect($rendered)->toContain('Subject: '.__('messages.passwords.mail.subject'))
        ->and($rendered)->toContain('To: array-reset@example.com')
        ->and($rendered)->toContain('/reset-password/');
});
