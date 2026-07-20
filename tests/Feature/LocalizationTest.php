<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['locale' => 'es', 'status' => 'Operación exitosa', 'status_type' => 'success'])
        ->get(route('dashboard'))
        ->assertSee('Bienvenido')
        ->assertSee('Ingresos')
        ->assertSee('Operación exitosa');

    $this->actingAs($user)
        ->withSession(['locale' => 'en', 'status' => 'Operation successful', 'status_type' => 'success'])
        ->get(route('dashboard'))
        ->assertSee('Welcome')
        ->assertSee('Incomes')
        ->assertSee('Operation successful');
});

it('validates unique email during registration with translations', function () {
    $existingUser = User::factory()->create([
        'email' => 'duplicate@example.com',
    ]);

    // Test Spanish translation
    $responseEs = $this->withSession(['locale' => 'es'])
        ->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

    $responseEs->assertSessionHasErrors([
        'email' => 'El campo correo electrónico ya ha sido registrado.',
    ]);

    // Test English translation
    $responseEn = $this->withSession(['locale' => 'en'])
        ->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

    $responseEn->assertSessionHasErrors([
        'email' => 'The email has already been taken.',
    ]);
});

it('preserves locale in session after logging out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['locale' => 'es'])
        ->post(route('logout'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('locale', 'es');

    // Check that the login page renders in Spanish
    $this->get(route('login'))
        ->assertSee('Iniciar sesión');
});
