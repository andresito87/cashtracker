<?php

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

it('requires name and email for profile update', function () {
    $user = createVerifiedUser();

    $request = new UpdateProfileRequest;
    $request->setUserResolver(fn () => $user);

    $validator = Validator::make([], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->keys())->toContain('name', 'email');
});

it('prevents taking an email used by another user', function () {
    $user = createVerifiedUser(['email' => 'myemail@example.com']);
    User::factory()->create(['email' => 'taken@example.com']);

    $request = new UpdateProfileRequest;
    $request->setUserResolver(fn () => $user);

    $validator = Validator::make([
        'name' => 'Valid Name',
        'email' => 'taken@example.com',
    ], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('email'))->toBeTrue();
});

it('allows keeping the user own email', function () {
    $user = createVerifiedUser(['email' => 'myemail@example.com']);

    $request = new UpdateProfileRequest;
    $request->setUserResolver(fn () => $user);

    $validator = Validator::make([
        'name' => 'Updated Name',
        'email' => 'myemail@example.com',
    ], $request->rules(), $request->messages());

    expect($validator->passes())->toBeTrue();
});
