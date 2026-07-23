<?php

use App\Models\Budget;
use Illuminate\Support\Facades\Gate;

it('forbids a regular user from viewing another users budget and renders custom 403 page', function () {
    $intruder = actingAsVerifiedUser();
    $owner = createVerifiedUser();
    $budget = Budget::factory()->for($owner)->create(['name' => 'Secret Budget']);

    $response = $this->get(route('budgets.show', $budget));

    $response->assertForbidden()
        ->assertSee(__('messages.error_403_title'))
        ->assertSee(__('messages.error_back_dashboard'));

    expect(Gate::forUser($intruder)->allows('view', $budget))->toBeFalse();
});

it('forbids a regular user from editing or updating another users budget and renders custom 403 page', function () {
    actingAsVerifiedUser();
    $owner = createVerifiedUser();
    $budget = Budget::factory()->for($owner)->create(['name' => 'Secret Budget']);

    $this->get(route('budgets.edit', $budget))
        ->assertForbidden()
        ->assertSee(__('messages.error_403_title'));

    $this->put(route('budgets.update', $budget), validBudgetPayload(['name' => 'Not Owned Budget']))
        ->assertForbidden()
        ->assertSee(__('messages.error_403_title'));

    expect($budget->fresh()->name)->toBe('Secret Budget');

    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'name' => 'Secret Budget',
    ]);
});

it('forbids a regular user from deleting another users budget and renders custom 403 page', function () {
    actingAsVerifiedUser();
    $owner = createVerifiedUser();
    $budget = Budget::factory()->for($owner)->create(['name' => 'Secret Budget']);

    $this->delete(route('budgets.destroy', $budget))
        ->assertForbidden()
        ->assertSee(__('messages.error_403_title'));

    expect($budget->fresh()->deleted_at)->toBeNull();

    $this->assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'deleted_at' => null,
    ]);
});

it('allows admins to bypass budget ownership policies', function () {
    $owner = createVerifiedUser();
    $admin = createAdminUser();
    $budget = Budget::factory()->for($owner)->create(['name' => 'Admin Accessible Budget']);

    $this->actingAs($admin);

    expect(Gate::forUser($admin)->allows('view', $budget))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $budget))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $budget))->toBeTrue();

    $this->get(route('budgets.show', $budget))
        ->assertSuccessful()
        ->assertSee('Admin Accessible Budget');
});

it('redirects guests away from budget routes', function () {
    $budget = Budget::factory()->create();

    $this->get(route('budgets.index'))->assertRedirect(route('login'));
    $this->get(route('budgets.create'))->assertRedirect(route('login'));
    $this->post(route('budgets.store'), validBudgetPayload())->assertRedirect(route('login'));
    $this->get(route('budgets.show', $budget))->assertRedirect(route('login'));
});

it('redirects authenticated but unverified users away from budget routes', function () {
    $budget = Budget::factory()->create();

    actingAsUnverifiedUser();

    $this->get(route('budgets.index'))->assertRedirect(route('verification.notice'));
    $this->get(route('budgets.create'))->assertRedirect(route('verification.notice'));
    $this->post(route('budgets.store'), validBudgetPayload())->assertRedirect(route('verification.notice'));
    $this->get(route('budgets.show', $budget))->assertRedirect(route('verification.notice'));
});
