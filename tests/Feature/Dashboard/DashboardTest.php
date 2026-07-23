<?php

use App\Models\Budget;

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
        ->assertSee(__('messages.manage_budgets_title'))
        ->assertSee(__('messages.create_budget'));
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
        ->assertSee('Operation successful');
});

it('displays only the authenticated users budgets on the dashboard', function () {
    $user = actingAsVerifiedUser();
    $otherUser = createVerifiedUser();

    $ownBudget = Budget::factory()->for($user)->create(['name' => 'Mi Presupuesto']);
    $otherBudget = Budget::factory()->for($otherUser)->create(['name' => 'Otro Presupuesto']);

    $response = $this->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Mi Presupuesto')
        ->assertDontSee('Otro Presupuesto');

    $response->assertViewHas('budgets', function ($budgets) use ($ownBudget, $otherBudget) {
        return $budgets->contains($ownBudget) && ! $budgets->contains($otherBudget);
    });
});
