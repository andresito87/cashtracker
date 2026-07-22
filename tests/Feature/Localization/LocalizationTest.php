<?php

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

it('switches locale through the language route', function () {
    $this->from('/')
        ->get(route('lang.switch', ['locale' => 'es']))
        ->assertRedirect('/')
        ->assertSessionHas('locale', 'es');
});

it('ignores unsupported locales in the language route', function () {
    $this->from('/')
        ->get(route('lang.switch', ['locale' => 'fr']))
        ->assertRedirect('/');

    expect(session('locale'))->toBeNull();
});

it('loads dashboard in different languages', function () {
    $user = createVerifiedUser();

    $this->actingAs($user)
        ->withSession(['locale' => 'es', 'status' => 'Operación exitosa', 'status_type' => 'success'])
        ->get(route('dashboard'))
        ->assertSee(__('messages.manage_budgets_title'))
        ->assertSee(__('messages.create_budget'))
        ->assertSee('Operación exitosa');

    $this->actingAs($user)
        ->withSession(['locale' => 'en', 'status' => 'Operation successful', 'status_type' => 'success'])
        ->get(route('dashboard'))
        ->assertSee(__('messages.manage_budgets_title'))
        ->assertSee(__('messages.create_budget'))
        ->assertSee('Operation successful');
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
