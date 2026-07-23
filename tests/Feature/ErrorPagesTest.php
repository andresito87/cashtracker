<?php

use App\Models\User;

it('renders 404 page with Spanish localized text when locale is es', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/budgets/999999?lang=es');

    $response->assertStatus(404)
        ->assertSee('Página No Encontrada')
        ->assertSee('No se encontró la página o el presupuesto solicitado.');
});

it('renders 404 page with English localized text when locale is en', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/budgets/999999?lang=en');

    $response->assertStatus(404)
        ->assertSee('Page Not Found')
        ->assertSee('The requested page or budget could not be found.');
});
