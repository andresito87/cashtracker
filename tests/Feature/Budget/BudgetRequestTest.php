<?php

use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use Illuminate\Support\Facades\Validator;

it('validates budget request rules successfully with valid data', function () {
    $request = new BudgetRequest;

    $validator = Validator::make(validBudgetPayload([
        'amount' => '150.25',
    ]), $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});

it('redirects back to budget create page with session errors when store payload is empty', function () {
    actingAsVerifiedUser();

    $this->from(route('budgets.create'))
        ->post(route('budgets.store'), [
            'name' => '',
            'amount' => '',
            'type' => '',
        ])
        ->assertRedirect(route('budgets.create'))
        ->assertSessionHasErrors(['name', 'amount', 'type']);
});

it('redirects back to budget edit page with session errors when update payload is empty', function () {
    $user = actingAsVerifiedUser();
    $budget = Budget::factory()->for($user)->create();

    $this->from(route('budgets.edit', $budget))
        ->put(route('budgets.update', $budget), [
            'name' => '',
            'amount' => '',
            'type' => '',
        ])
        ->assertRedirect(route('budgets.edit', $budget))
        ->assertSessionHasErrors(['name', 'amount', 'type']);
});

it('rejects budget name exceeding 255 characters', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'name' => str_repeat('a', 256),
    ]))->assertSessionHasErrors(['name']);
});

it('rejects non-numeric budget amounts', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'amount' => 'not-a-number',
    ]))->assertSessionHasErrors(['amount']);
});

it('rejects amounts with more than 2 decimal places', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'amount' => '150.999',
    ]))->assertSessionHasErrors(['amount']);
});

it('rejects zero or negative amounts', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'amount' => 0,
    ]))->assertSessionHasErrors(['amount']);

    $this->post(route('budgets.store'), validBudgetPayload([
        'amount' => -10,
    ]))->assertSessionHasErrors(['amount']);
});

it('rejects invalid budget type values', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'type' => 'non-existent-type',
    ]))->assertSessionHasErrors(['type']);
});

it('rejects descriptions exceeding max character length', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'description' => str_repeat('a', 1001),
    ]))->assertSessionHasErrors(['description']);
});

it('provides custom localized validation messages in Spanish and English', function () {
    actingAsVerifiedUser();

    // Spanish
    app()->setLocale('es');
    $this->from(route('budgets.create'))
        ->withSession(['locale' => 'es'])
        ->post(route('budgets.store'))
        ->assertRedirect(route('budgets.create'))
        ->assertSessionHasErrors([
            'name' => __('messages.validation_name_required'),
            'amount' => __('messages.validation_amount_required'),
            'type' => __('messages.validation_type_required'),
        ]);

    // English
    app()->setLocale('en');
    $this->from(route('budgets.create'))
        ->withSession(['locale' => 'en'])
        ->post(route('budgets.store'))
        ->assertRedirect(route('budgets.create'))
        ->assertSessionHasErrors([
            'name' => __('messages.validation_name_required'),
            'amount' => __('messages.validation_amount_required'),
            'type' => __('messages.validation_type_required'),
        ]);
});
