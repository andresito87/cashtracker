<?php

use App\Models\Budget;
use App\Models\User;
use App\Policies\BudgetPolicy;

beforeEach(function () {
    $this->policy = new BudgetPolicy;
});

it('allows admins to view any budget listing via viewAny', function () {
    $admin = User::factory()->admin()->make();
    $user = User::factory()->make();

    expect($this->policy->viewAny($admin))->toBeTrue()
        ->and($this->policy->viewAny($user))->toBeFalse();
});

it('allows owners to view update and delete their budgets', function () {
    $owner = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();

    expect($this->policy->view($owner, $budget))->toBeTrue()
        ->and($this->policy->update($owner, $budget))->toBeTrue()
        ->and($this->policy->delete($owner, $budget))->toBeTrue();
});

it('denies non owners from viewing updating or deleting budgets', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();

    expect($this->policy->view($otherUser, $budget))->toBeFalse()
        ->and($this->policy->update($otherUser, $budget))->toBeFalse()
        ->and($this->policy->delete($otherUser, $budget))->toBeFalse();
});

it('allows any authenticated user to create budgets', function () {
    $user = User::factory()->make();

    expect($this->policy->create($user))->toBeTrue();
});
