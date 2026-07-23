<?php

use App\Models\Budget;

it('forbids a regular user from viewing another users budget and renders custom 403 page', function () {
    actingAsVerifiedUser();
    $otherUser = createVerifiedUser();
    $otherBudget = Budget::factory()->for($otherUser)->create(['name' => 'Secret Budget']);

    $response = $this->get(route('budgets.show', $otherBudget));

    $response->assertStatus(403)
        ->assertSee(__('messages.error_403_title'))
        ->assertSee(__('messages.error_back_dashboard'));
});

it('forbids a regular user from updating another users budget and renders custom 403 page', function () {
    actingAsVerifiedUser();
    $otherUser = createVerifiedUser();
    $otherBudget = Budget::factory()->for($otherUser)->create(['name' => 'Secret Budget']);

    $response = $this->put(route('budgets.update', $otherBudget), validBudgetPayload([
        'name' => 'Not Owned Budget',
    ]));

    $response->assertStatus(403)
        ->assertSee(__('messages.error_403_title'));
});

it('forbids a regular user from deleting another users budget and renders custom 403 page', function () {
    actingAsVerifiedUser();
    $otherUser = createVerifiedUser();
    $otherBudget = Budget::factory()->for($otherUser)->create(['name' => 'Secret Budget']);

    $response = $this->delete(route('budgets.destroy', $otherBudget));

    $response->assertStatus(403)
        ->assertSee(__('messages.error_403_title'));
});
