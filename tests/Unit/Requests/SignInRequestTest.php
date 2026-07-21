<?php

use App\Http\Requests\SignInRequest;
use Illuminate\Support\Facades\Validator;

it('requires email and password', function () {
    $validator = Validator::make([], (new SignInRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('email', 'password');
});

it('rejects invalid email format', function () {
    $validator = Validator::make([
        'email' => 'not-an-email',
        'password' => 'secret',
    ], (new SignInRequest)->rules());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});

it('passes with valid credentials payload', function () {
    $validator = Validator::make([
        'email' => 'user@example.com',
        'password' => 'secret',
    ], (new SignInRequest)->rules());

    expect($validator->passes())->toBeTrue();
});
