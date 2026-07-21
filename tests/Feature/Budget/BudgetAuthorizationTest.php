<?php

use App\Models\Budget;
use Illuminate\Support\Facades\Gate;

it('forbids regular users from viewing another users budget', function () {
    $owner = createVerifiedUser();
    $intruder = actingAsVerifiedUser();
    $budget = Budget::factory()->for($owner)->create();

    $this->get(route('budgets.show', $budget))->assertForbidden();
    $this->get(route('budgets.edit', $budget))->assertForbidden();
    $this->put(route('budgets.update', $budget), validBudgetPayload())->assertForbidden();
    $this->delete(route('budgets.destroy', $budget))->assertForbidden();

    expect(Gate::forUser($intruder)->allows('view', $budget))->toBeFalse();
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
