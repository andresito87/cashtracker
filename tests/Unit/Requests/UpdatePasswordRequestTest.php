<?php

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Validator;

it('requires current_password, password and confirmation', function () {
    $request = new UpdatePasswordRequest;

    $validator = Validator::make([], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('current_password', 'password');
});

it('rejects weak new passwords', function () {
    $request = new UpdatePasswordRequest;

    $validator = Validator::make([
        'current_password' => 'OldP@ssw0rd123!',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('password'))->toBeTrue();
});

it('passes with a valid current password and strong new password', function () {
    $user = createVerifiedUser(['password' => 'OldP@ssw0rd123!']);
    $this->actingAs($user);

    $request = new UpdatePasswordRequest;
    $request->setUserResolver(fn () => $user);

    $validator = Validator::make([
        'current_password' => 'OldP@ssw0rd123!',
        'password' => 'NewSecureP@ssw0rd2026!',
        'password_confirmation' => 'NewSecureP@ssw0rd2026!',
    ], $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});
