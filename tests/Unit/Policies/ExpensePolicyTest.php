<?php

use App\Models\Budget;
use App\Models\Expense;
use App\Models\User;
use App\Policies\ExpensePolicy;

beforeEach(function () {
    $this->policy = new ExpensePolicy;
});

it('allows budget owner to create update and delete expenses', function () {
    $owner = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();
    $expense = Expense::factory()->for($budget)->create();

    expect($this->policy->create($owner, $budget)->allowed())->toBeTrue()
        ->and($this->policy->update($owner, $expense)->allowed())->toBeTrue()
        ->and($this->policy->delete($owner, $expense)->allowed())->toBeTrue();
});

it('allows admin users to create update and delete any expense', function () {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();
    $expense = Expense::factory()->for($budget)->create();

    expect($this->policy->create($admin, $budget)->allowed())->toBeTrue()
        ->and($this->policy->update($admin, $expense)->allowed())->toBeTrue()
        ->and($this->policy->delete($admin, $expense)->allowed())->toBeTrue();
});

it('denies non-owners and non-admins from creating updating or deleting expenses', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $budget = Budget::factory()->for($owner)->create();
    $expense = Expense::factory()->for($budget)->create();

    expect($this->policy->create($otherUser, $budget)->denied())->toBeTrue()
        ->and($this->policy->update($otherUser, $expense)->denied())->toBeTrue()
        ->and($this->policy->delete($otherUser, $expense)->denied())->toBeTrue();
});
