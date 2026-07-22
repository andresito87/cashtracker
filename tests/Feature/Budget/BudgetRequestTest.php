<?php

use App\Http\Requests\BudgetRequest;
use Illuminate\Support\Facades\Validator;

it('validates budget request rules successfully with valid data', function () {
    $request = new BudgetRequest;

    $validator = Validator::make(validBudgetPayload([
        'amount' => '150.25',
    ]), $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});

it('rejects amounts with more than 2 decimal places', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'amount' => '150.999',
    ]))
        ->assertSessionHasErrors(['amount']);
});

it('rejects zero or negative amounts', function () {
    actingAsVerifiedUser();

    $this->post(route('budgets.store'), validBudgetPayload([
        'amount' => 0,
    ]))
        ->assertSessionHasErrors(['amount']);
});

it('provides custom localized validation messages', function () {
    actingAsVerifiedUser();

    app()->setLocale('es');

    $response = $this->withSession(['locale' => 'es'])
        ->post(route('budgets.store'));

    $response->assertSessionHasErrors([
        'name' => __('messages.validation_name_required'),
        'amount' => __('messages.validation_amount_required'),
        'currency' => __('messages.validation_currency_required'),
        'type' => __('messages.validation_type_required'),
    ]);
});
