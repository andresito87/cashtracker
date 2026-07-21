<?php

use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('redirects unverified users to the verification notice', function () {
    actingAsUnverifiedUser();

    $this->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));

    $this->get(route('budgets.index'))
        ->assertRedirect(route('verification.notice'));
});

it('allows verified users to access protected routes', function () {
    actingAsVerifiedUser();

    $this->get(route('dashboard'))->assertSuccessful();
});

it('verifies a user email through the signed verification url', function () {
    $user = createUnverifiedUser();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $this->actingAs($user)
        ->get($verificationUrl)
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', __('messages.status_verified'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('rejects invalid verification signatures', function () {
    $user = createUnverifiedUser();

    $this->actingAs($user)
        ->get(route('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]))
        ->assertForbidden();
});

it('resends the verification notification for unverified users', function () {
    Notification::fake();

    $user = actingAsUnverifiedUser();

    $this->from(route('verification.notice'))
        ->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('status', 'verification-link-sent');

    // Allows disabled inspection for unhandled exceptions in this context,
    // as the notification assertion is expected to be handled by the test framework.
    /** @noinspection PhpUnhandledExceptionInspection */
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('shows the verification notice page to authenticated unverified users', function () {
    actingAsUnverifiedUser();

    $this->get(route('verification.notice'))
        ->assertSuccessful()
        ->assertSee(__('messages.verify_email'));
});

it('shows the verification notice page in Spanish for unverified users', function () {
    actingAsUnverifiedUser();

    $this->withSession(['locale' => 'es'])
        ->get(route('verification.notice'))
        ->assertSuccessful()
        ->assertSee(__('messages.verify_email'));
});
