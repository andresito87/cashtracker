<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    config(['app.url' => 'http://cashtracker.test']);
});

it('renders the reset password form for a valid signed URL with a valid token', function () {
    app()->setLocale('en');

    $user = createVerifiedUser(['email' => 'reset@example.com']);
    $token = Password::createToken($user);

    $url = URL::temporarySignedRoute('password.reset', now()->addMinutes(60), [
        'token' => $token,
        'email' => $user->email,
    ]);

    $this->get($url)
        ->assertSuccessful()
        ->assertSee(__('messages.passwords.reset.title'))
        ->assertSee('value="'.$user->email.'"', false);
});

it('forbids an unsigned reset URL with 403', function () {
    $user = createVerifiedUser(['email' => 'reset@example.com']);
    $token = Password::createToken($user);

    $this->get(route('password.reset', [
        'token' => $token,
        'email' => $user->email,
    ]))->assertForbidden();
});

it('forbids an expired signed reset URL with 403', function () {
    $user = createVerifiedUser(['email' => 'reset@example.com']);
    $token = Password::createToken($user);

    $url = URL::temporarySignedRoute('password.reset', now()->subMinutes(61), [
        'token' => $token,
        'email' => $user->email,
    ]);

    $this->get($url)->assertForbidden();
});

it('resets the password, signs the user in, and redirects to the dashboard', function () {
    $user = createVerifiedUser([
        'email' => 'reset@example.com',
        'password' => 'OldP@ssw0rd123!',
    ]);
    $token = Password::createToken($user);

    $newPassword = 'NewSecureP@ssw0rd2026!';

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', __('messages.passwords.reset_success'));

    $this->assertAuthenticatedAs($user);

    $fresh = $user->fresh();
    expect(Hash::check($newPassword, $fresh->password))->toBeTrue()
        ->and(Hash::check('OldP@ssw0rd123!', $fresh->password))->toBeFalse();
});

it('rejects a wrong token with a generic error and stays logged out', function () {
    $user = createVerifiedUser(['email' => 'reset@example.com']);
    Password::createToken($user);

    $this->post(route('password.update'), [
        'token' => 'wrong-token',
        'email' => $user->email,
        'password' => 'NewSecureP@ssw0rd2026!',
        'password_confirmation' => 'NewSecureP@ssw0rd2026!',
    ])
        ->assertSessionHasErrors(['email' => __('messages.passwords.reset_failed')]);

    $this->assertGuest();
});

it('rejects a non-matching password confirmation with a confirmation error', function () {
    $user = createVerifiedUser(['email' => 'reset@example.com']);
    $token = Password::createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewSecureP@ssw0rd2026!',
        'password_confirmation' => 'DifferentP@ssw0rd1!',
    ])->assertSessionHasErrors(['password']);
});

it('rejects missing fields with required validation errors', function () {
    $this->post(route('password.update'))
        ->assertSessionHasErrors(['email', 'password', 'password_confirmation', 'token']);
});

it('rejects a weak password that fails the Password rule', function () {
    $user = createVerifiedUser(['email' => 'reset@example.com']);
    $token = Password::createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ])->assertSessionHasErrors(['password']);
});

it('rejects a re-use of an already-consumed token with the generic error', function () {
    $user = createVerifiedUser(['email' => 'reset@example.com']);
    $token = Password::createToken($user);
    $newPassword = 'NewSecureP@ssw0rd2026!';

    // First use succeeds and consumes the token.
    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ])->assertRedirect(route('dashboard'));

    $this->actingAs($user)->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'AnotherP@ssw0rd9!',
        'password_confirmation' => 'AnotherP@ssw0rd9!',
    ])
        ->assertSessionHasErrors(['email' => __('messages.passwords.reset_failed')]);
});
