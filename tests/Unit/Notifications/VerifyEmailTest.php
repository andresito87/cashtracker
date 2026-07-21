<?php

use App\Models\User;
use App\Notifications\VerifyEmail;

it('builds a signed verification mail message in English', function () {
    config(['app.url' => 'http://cashtracker.test']);
    app()->setLocale('en');

    $user = User::factory()->unverified()->create();
    $notification = new VerifyEmail;
    $mail = $notification->toMail($user);

    expect($notification->via($user))->toBe(['mail'])
        ->and($mail->subject)->toBe('Confirm your account at CashTracker')
        ->and($mail->greeting)->toBe('Hello!')
        ->and($mail->actionText)->toBe('Verify account')
        ->and($mail->actionUrl)->toContain('/verify-email/')
        ->and($mail->actionUrl)->toContain('signature=');
});

it('builds a signed verification mail message in Spanish', function () {
    config(['app.url' => 'http://cashtracker.test']);
    app()->setLocale('es');

    $user = User::factory()->unverified()->create();
    $notification = new VerifyEmail;
    $mail = $notification->toMail($user);

    expect($notification->via($user))->toBe(['mail'])
        ->and($mail->subject)->toBe('Confirma tu cuenta en CashTracker')
        ->and($mail->greeting)->toBe('¡Hola!')
        ->and($mail->actionText)->toBe('Verificar cuenta')
        ->and($mail->actionUrl)->toContain('/verify-email/')
        ->and($mail->actionUrl)->toContain('signature=');
});
