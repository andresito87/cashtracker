<?php

use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects unauthenticated users trying to access profile settings', function () {
    $this->get(route('settings.profile'))
        ->assertRedirect(route('login'));
});

it('renders profile settings page for authenticated and verified user', function () {
    actingAsVerifiedUser();

    $this->get(route('settings.profile'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Settings/UpdateProfile')
        );
});

it('can update profile information without changing email', function () {
    $user = actingAsVerifiedUser([
        'name' => 'Old Name',
        'email' => 'same.email@example.com',
    ]);

    $response = $this->put(route('settings.profile.update'), [
        'name' => 'New Name',
        'email' => 'same.email@example.com',
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('same.email@example.com')
        ->and($user->email_verified_at)->not->toBeNull();
});

it('resets email verification status and sends notification when email changes', function () {
    Notification::fake();

    $user = actingAsVerifiedUser([
        'name' => 'John Doe',
        'email' => 'john.old@example.com',
    ]);

    $response = $this->put(route('settings.profile.update'), [
        'name' => 'John Doe',
        'email' => 'john.new@example.com',
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect($user->email)->toBe('john.new@example.com')
        ->and($user->email_verified_at)->toBeNull()
        ->and($user)->toHaveBeenNotifiedOf(VerifyEmail::class);
});
