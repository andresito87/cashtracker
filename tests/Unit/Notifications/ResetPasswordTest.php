<?php

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;

beforeEach(function () {
    config(['app.url' => 'http://cashtracker.test']);
});

it('is queueable and uses the mail channel', function () {
    $user = User::factory()->create();
    $notification = new ResetPassword('test-token');

    expect($notification)->toBeInstanceOf(ShouldQueue::class)
        ->and($notification->via($user))->toBe(['mail']);
});

it('keeps the reset hook signature compatible with the framework', function () {
    $parameter = (new ReflectionMethod(User::class, 'sendPasswordResetNotification'))->getParameters()[0];

    expect($parameter->getType())->toBeNull();
});

it('builds a signed 60-minute reset mail in English with a brand-purple CTA', function () {
    app()->setLocale('en');

    $user = User::factory()->create(['email' => 'reset@example.com']);
    $notification = new ResetPassword('test-token');
    $mail = $notification->toMail($user);

    $rendered = (string) $mail->render();

    expect($mail->subject)->toBe(__('messages.passwords.mail.subject'))
        ->and($mail->greeting)->toBe(__('messages.passwords.mail.greeting'))
        ->and($mail->salutation)->toBe(__('messages.passwords.mail.salutation'))
        ->and($rendered)->toContain('#4C1D95')
        ->and($rendered)->toContain('/auth/reset-password/test-token')
        ->and($rendered)->toContain('signature=')
        ->and($rendered)->toContain(__('messages.passwords.mail.action'));
});

it('builds a signed 60-minute reset mail in Spanish with a brand-purple CTA', function () {
    app()->setLocale('es');

    $user = User::factory()->create(['email' => 'reset@example.com']);
    $notification = new ResetPassword('test-token');
    $mail = $notification->toMail($user);

    $rendered = (string) $mail->render();

    expect($mail->subject)->toBe(__('messages.passwords.mail.subject'))
        ->and($mail->greeting)->toBe(__('messages.passwords.mail.greeting'))
        ->and($rendered)->toContain('#4C1D95')
        ->and($rendered)->toContain('/auth/reset-password/test-token')
        ->and($rendered)->toContain('signature=')
        ->and($rendered)->toContain(__('messages.passwords.mail.action'));
});
