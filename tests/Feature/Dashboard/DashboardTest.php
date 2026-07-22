<?php

it('redirects guests to the login page', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('shows the dashboard for verified users', function () {
    $user = actingAsVerifiedUser([
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
