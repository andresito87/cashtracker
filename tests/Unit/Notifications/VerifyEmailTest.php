<?php

use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Queue\ShouldQueue;

beforeEach(function () {
    config(['app.url' => 'http://cashtracker.test']);
});

it('is queueable and uses the mail channel', function () {
    $user = User::factory()->unverified()->create();
    $notification = new VerifyEmail;

    expect($notification)->toBeInstanceOf(ShouldQueue::class)
        ->and($notification->via($user))->toBe(['mail']);
});

it('keeps the effective queue connection capable of processing queued notifications', function () {
    $default = config('queue.default');

    expect($default)->not->toBeNull()
        ->and($default)->not->toBe('')
        ->and(config("queue.connections.$default.driver"))->not->toBeNull();
});

it('builds a signed verification mail in English with a brand-purple CTA', function () {
    app()->setLocale('en');

    $user = User::factory()->unverified()->create();
    $notification = new VerifyEmail;
    $mail = $notification->toMail($user);

    $rendered = (string) $mail->render();

    expect($mail->subject)->toBe(__('messages.email_verify_subject'))
        ->and($mail->greeting)->toBe(__('messages.email_verify_greeting'))
        ->and($mail->salutation)->toBe(__('messages.email_verify_salutation'))
        ->and($rendered)->toContain('#4C1D95')
        ->and($rendered)->toContain('/verify-email/')
        ->and($rendered)->toContain('signature=')
        ->and($rendered)->toContain(__('messages.email_verify_action'));
});

it('builds a signed verification mail in Spanish with a brand-purple CTA', function () {
    app()->setLocale('es');

    $user = User::factory()->unverified()->create();
    $notification = new VerifyEmail;
    $mail = $notification->toMail($user);

    $rendered = (string) $mail->render();

    expect($mail->subject)->toBe(__('messages.email_verify_subject'))
        ->and($mail->greeting)->toBe(__('messages.email_verify_greeting'))
        ->and($rendered)->toContain('#4C1D95')
        ->and($rendered)->toContain('/verify-email/')
        ->and($rendered)->toContain('signature=')
        ->and($rendered)->toContain(__('messages.email_verify_action'));
});
