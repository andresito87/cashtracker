<?php

use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Support\Facades\Validator;

it('is always authorized', function () {
    expect((new ForgotPasswordRequest)->authorize())->toBeTrue();
});

it('requires a valid email and no longer checks existence on the users table', function () {
    $request = new ForgotPasswordRequest;

    $rules = $request->rules();

    expect($rules['email'])->toContain('required', 'email')
        ->and($rules['email'])->not->toContain('exists:users,email');
});

it('fails validation when the email is empty or invalid', function () {
    $request = new ForgotPasswordRequest;

    $empty = Validator::make(['email' => ''], $request->rules(), $request->messages());
    $invalid = Validator::make(['email' => 'not-an-email'], $request->rules(), $request->messages());

    expect($empty->fails())->toBeTrue()
        ->and($empty->errors()->has('email'))->toBeTrue()
        ->and($invalid->fails())->toBeTrue()
        ->and($invalid->errors()->has('email'))->toBeTrue();
});

it('passes validation for any syntactically valid email regardless of existence', function () {
    $request = new ForgotPasswordRequest;

    $validator = Validator::make(['email' => 'nobody@example.com'], $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});

it('resolves every validation message through translation keys', function () {
    app()->setLocale('en');

    $messages = (new ForgotPasswordRequest)->messages();

    foreach (['email.required', 'email.email'] as $key) {
        expect($messages[$key])->not->toBe($key)
            ->and($messages[$key])->not->toBeEmpty();
    }
});
