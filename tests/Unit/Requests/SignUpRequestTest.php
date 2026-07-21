<?php

use App\Http\Requests\SignUpRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

it('requires name email and password confirmation', function () {
    $validator = Validator::make([], (new SignUpRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('name', 'email', 'password');
});

it('rejects short names and duplicate emails', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $validator = Validator::make([
        'name' => 'A',
        'email' => 'taken@example.com',
        'password' => 'SecureP@ssw0rd2026!XyZ',
        'password_confirmation' => 'SecureP@ssw0rd2026!XyZ',
    ], (new SignUpRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});

it('rejects weak passwords', function () {
    $validator = Validator::make([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ], (new SignUpRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});

it('passes with a valid registration payload', function () {
    $validator = Validator::make(validRegistrationPayload(), (new SignUpRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
