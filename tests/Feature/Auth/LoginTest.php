<?php

it('renders the login form for guests', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee(__('messages.sign_in'));
});

it('authenticates a verified user with valid credentials', function () {
    $user = createVerifiedUser();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('status', __('messages.login_success'));

    $this->assertAuthenticatedAs($user);
});

it('redirects an unverified user to the verification notice after a successful login', function () {
    $user = actingAsUnverifiedUser();

    $this->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials and preserves the email input', function () {
    $user = createVerifiedUser();

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('login')
        ->assertSessionHas('_old_input.email', $user->email);

    $this->assertGuest();
});

it('rejects login attempts with a non-existent email and returns the same generic error', function () {
    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => 'nonexistent@example.com',
            'password' => 'any-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('login')
        ->assertSessionHas('_old_input.email', 'nonexistent@example.com');

    $this->assertGuest();
});

it('validates required login fields', function () {
    $this->post(route('login.store'))
        ->assertSessionHasErrors(['email', 'password']);
});

it('logs out the user and preserves locale in session', function () {
    $user = createVerifiedUser();

    $this->actingAs($user)
        ->withSession(['locale' => 'es'])
        ->post(route('logout'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('locale', 'es');

    $this->assertGuest();
});
