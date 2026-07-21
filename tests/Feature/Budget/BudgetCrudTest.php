<?php

use App\Models\Budget;

it('lists only the authenticated users budgets', function () {
    $user = actingAsVerifiedUser();
    $otherUser = createVerifiedUser();

    $ownBudget = Budget::factory()->for($user)->create(['name' => 'My Budget']);
    Budget::factory()->for($otherUser)->create(['name' => 'Other Budget']);

    $this->get(route('budgets.index'))
        ->assertSuccessful()
        ->assertSee('My Budget')
        ->assertDontSee('Other Budget');
});

it('creates a budget for the authenticated user', function () {
    $user = actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'name' => 'Travel Fund',
    ]))
        ->assertRedirect(route('budgets.index'))
        ->assertSessionHas('status', __('messages.budget_created'));

    $budget = Budget::query()->first();

    expect($budget)->not->toBeNull()
        ->and($budget->user_id)->toBe($user->id)
        ->and($budget->name)->toBe('Travel Fund');
});

it('updates an owned budget', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->put(route('budgets.update', $budget), validBudgetPayload([
        'name' => 'Updated Budget',
        'amount' => 500,
    ]))
        ->assertRedirect(route('budgets.show', $budget))
        ->assertSessionHas('status', __('messages.budget_updated'));

    expect($budget->fresh()->name)->toBe('Updated Budget')
        ->and($budget->fresh()->amount)->toBe('500.00');
});

it('deletes an owned budget', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->delete(route('budgets.destroy', $budget))
        ->assertRedirect(route('budgets.index'))
        ->assertSessionHas('status', __('messages.budget_deleted'));

    expect(Budget::query()->find($budget->id))->toBeNull();
});

it('validates budget input on create', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), [])
        ->assertSessionHasErrors(['name', 'amount']);
});

it('rejects negative budget amounts', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'amount' => -10,
    ]))->assertSessionHasErrors(['amount']);
});

it('shows the create budget form for verified users', function () {
    actingAsVerifiedUser();

    $this->get(route('budgets.create'))
        ->assertSuccessful();
});

it('shows an owned budget detail page', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create(['name' => 'Emergency Fund']);

    $this->get(route('budgets.show', $budget))
        ->assertSuccessful()
        ->assertSee('Emergency Fund');
});
