<?php

use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

it('is always authorized', function () {
    expect((new ResetPasswordRequest)->authorize())->toBeTrue();
});

it('defines the expected validation rules', function () {
    $rules = (new ResetPasswordRequest)->rules();

    expect($rules)->toHaveKeys(['email', 'password', 'password_confirmation', 'token'])
        ->and($rules['email'])->toContain('required', 'email')
        ->and($rules['token'])->toContain('required')
		// The password rule uses the Password rule object
        ->and(collect($rules['password'])->contains(fn ($rule) => $rule instanceof Password))->toBeTrue()
        ->and($rules['password'])->toContain('required', 'confirmed');

});

it('fails validation when every field is missing', function () {
    $request = new ResetPasswordRequest;

    $validator = Validator::make([], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('email', 'password', 'password_confirmation', 'token');
});

it('rejects a non-matching confirmation', function () {
    $request = new ResetPasswordRequest;

    $validator = Validator::make([
        'token' => 'valid-token',
        'email' => 'reset@example.com',
        'password' => 'NewSecureP@ssw0rd2026!',
        'password_confirmation' => 'DifferentP@ssw0rd1!',
    ], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});

it('resolves every validation message through translation keys', function () {
    app()->setLocale('en');

    $messages = (new ResetPasswordRequest)->messages();

    foreach (['email.required', 'email.email', 'password.required', 'password.confirmed', 'password_confirmation.required', 'token.required'] as $key) {
        expect($messages)->toHaveKey($key)
            ->and($messages[$key])->not->toBeEmpty()
            ->and($messages[$key])->not->toBe($key);
    }
});
