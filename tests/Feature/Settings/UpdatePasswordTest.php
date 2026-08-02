<?php

use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects unauthenticated users trying to access password settings', function () {
    $this->get(route('settings.password'))
        ->assertRedirect(route('login'));
});

it('renders password settings page for authenticated user', function () {
    actingAsVerifiedUser();

    $this->get(route('settings.password'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Settings/UpdatePassword')
        );
});

it('can update password with valid current password and strong new password', function () {
    $user = actingAsVerifiedUser([
        'password' => 'OldP@ssw0rd123!',
    ]);

    $response = $this->put(route('settings.password.update'), [
        'current_password' => 'OldP@ssw0rd123!',
        'password' => 'NewSecureP@ssw0rd2026!',
        'password_confirmation' => 'NewSecureP@ssw0rd2026!',
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect(Hash::check('NewSecureP@ssw0rd2026!', $user->password))->toBeTrue();
});

it('rejects password update if current password is incorrect', function () {
    $user = actingAsVerifiedUser([
        'password' => 'OldP@ssw0rd123!',
    ]);

    $response = $this->put(route('settings.password.update'), [
        'current_password' => 'WrongCurrentPassword!',
        'password' => 'NewSecureP@ssw0rd2026!',
        'password_confirmation' => 'NewSecureP@ssw0rd2026!',
    ]);

    $response->assertSessionHasErrors('current_password');

    $user->refresh();
    expect(Hash::check('OldP@ssw0rd123!', $user->password))->toBeTrue();
});
