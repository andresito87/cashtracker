<?php

use Inertia\Testing\AssertableInertia as Assert;

it('can switch languages via query parameter and persists in session', function () {
    $response = $this->get('/?lang=es');

    $response->assertOk();
    $response->assertSessionHas('locale', 'es');
    expect(app()->getLocale())->toBe('es');

    $response2 = $this->get('/?lang=en');

    $response2->assertOk();
    $response2->assertSessionHas('locale', 'en');
    expect(app()->getLocale())->toBe('en');
});

it('loads dashboard in different languages', function () {
    $user = createVerifiedUser();

    $this->actingAs($user)
        ->withSession(['locale' => 'es', 'status' => 'Operación exitosa', 'status_type' => 'success'])
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('flash.status', 'Operación exitosa')
            ->where('flash.status_type', 'success')
        );

    $this->actingAs($user)
        ->withSession(['locale' => 'en', 'status' => 'Operation successful', 'status_type' => 'success'])
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('flash.status', 'Operation successful')
            ->where('flash.status_type', 'success')
        );
});

it('validates unique email during registration with translations', function () {
    createVerifiedUser([
        'email' => 'duplicate@example.com',
    ]);

    app()->setLocale('es');
    $this->withSession(['locale' => 'es'])
        ->post(route('register.store'), validRegistrationPayload([
            'email' => 'duplicate@example.com',
        ]))
        ->assertSessionHasErrors([
            'email' => __('validation.unique', ['attribute' => __('validation.attributes.email')]),
        ]);

    app()->setLocale('en');
    $this->withSession(['locale' => 'en'])
        ->post(route('register.store'), validRegistrationPayload([
            'email' => 'duplicate@example.com',
        ]))
        ->assertSessionHasErrors([
            'email' => __('validation.unique', ['attribute' => __('validation.attributes.email')]),
        ]);
});

it('preserves locale in session after logging out', function () {
    $user = createVerifiedUser();

    $this->actingAs($user)
        ->withSession(['locale' => 'es'])
        ->post(route('logout'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('locale', 'es');

    app()->setLocale('es');
    $this->get(route('login'))
        ->assertSee(__('messages.sign_in'));
});

it('shares the locale whitelist and default locale with every Inertia page', function () {
    $user = createVerifiedUser();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('available_locales', config('app.available_locales'))
            ->where('default_locale', config('app.locale'))
        );
});

it('shares the locale whitelist as a plain array with the keys from config', function () {
    $user = createVerifiedUser();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('available_locales', ['en', 'es'])
            ->where('default_locale', config('app.locale', 'en'))
        );
});
