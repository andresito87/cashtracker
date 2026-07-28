<?php

use App\Models\Budget;
use Inertia\Testing\AssertableInertia as Assert;

it('shares null flash when no operation was performed', function () {
    actingAsVerifiedUser();

    $this->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('flash.status', null)
        );
});

it('shares flash status as success after creating a budget', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->withSession([
        'status' => __('messages.budget_created'),
        'status_type' => 'success',
    ])
        ->get(route('budgets.show', $budget))
        ->assertInertia(fn (Assert $page) => $page
            ->where('flash.status', __('messages.budget_created'))
            ->where('flash.status_type', 'success')
        );
});

it('shares flash status as success after updating a budget', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->withSession([
        'status' => __('messages.budget_updated'),
        'status_type' => 'success',
    ])
        ->get(route('budgets.show', $budget))
        ->assertInertia(fn (Assert $page) => $page
            ->where('flash.status', __('messages.budget_updated'))
            ->where('flash.status_type', 'success')
        );
});

it('shares flash status as success after deleting a budget', function () {
    actingAsVerifiedUser();

    $this->withSession([
        'status' => __('messages.budget_deleted'),
        'status_type' => 'success',
    ])
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('flash.status', __('messages.budget_deleted'))
            ->where('flash.status_type', 'success')
        );
});

it('shares flash status as success after creating an expense', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->withSession([
        'status' => __('messages.expense_added'),
        'status_type' => 'success',
    ])
        ->get(route('budgets.show', $budget))
        ->assertInertia(fn (Assert $page) => $page
            ->where('flash.status', __('messages.expense_added'))
            ->where('flash.status_type', 'success')
        );
});

it('shares flash status as success after updating an expense', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->withSession([
        'status' => __('messages.expense_updated'),
        'status_type' => 'success',
    ])
        ->get(route('budgets.show', $budget))
        ->assertInertia(fn (Assert $page) => $page
            ->where('flash.status', __('messages.expense_updated'))
            ->where('flash.status_type', 'success')
        );
});

it('shares flash status as success after deleting an expense', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->withSession([
        'status' => __('messages.expense_deleted'),
        'status_type' => 'success',
    ])
        ->get(route('budgets.show', $budget))
        ->assertInertia(fn (Assert $page) => $page
            ->where('flash.status', __('messages.expense_deleted'))
            ->where('flash.status_type', 'success')
        );
});
