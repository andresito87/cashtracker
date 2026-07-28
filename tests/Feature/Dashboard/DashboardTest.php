<?php

use App\Models\Budget;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to the login page', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('shows the dashboard for verified users', function () {
    actingAsVerifiedUser([
        'name' => 'Alejandro Rivera',
    ]);

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
        );
});

it('displays flash status messages on the dashboard', function () {
    actingAsVerifiedUser();

    $this->withSession([
        'locale' => 'en',
        'status' => 'Operation successful',
        'status_type' => 'success',
    ])
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('flash.status', 'Operation successful')
            ->where('flash.status_type', 'success')
        );
});

it('displays only the authenticated users budgets on the dashboard', function () {
    $user = actingAsVerifiedUser();
    $otherUser = createVerifiedUser();

    Budget::factory()->for($user)->create(['name' => 'Mi Presupuesto']);
    Budget::factory()->for($otherUser)->create(['name' => 'Otro Presupuesto']);

    $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('budgets', 1)
            ->where('budgets.0.name', 'Mi Presupuesto')
        );
});
