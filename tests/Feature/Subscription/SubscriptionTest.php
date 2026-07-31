<?php

use App\Enums\Currency;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Cashier\Subscription;

test('guest cannot access plans page', function () {
    $response = $this->get(route('plans'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can access plans page', function () {
    actingAsVerifiedUser();

    $response = $this->get(route('plans'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Subscriptions/Manage')
            ->has('subscription')
        );
});

test('authenticated user can access subscription manage page', function () {
    actingAsVerifiedUser();

    $response = $this->get(route('subscription.manage'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Subscriptions/Manage')
            ->has('subscription')
        );
});

test('checkout route aborts with 404 for invalid plan', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.checkout', ['plan' => 'super_ultra']));

    $response->assertNotFound();
});

test('checkout route redirects for valid monthly plan', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.checkout', ['plan' => 'monthly']));

    $response->assertRedirect();
});

test('checkout route redirects for valid yearly plan', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.checkout', ['plan' => 'yearly']));

    $response->assertRedirect();
});

test('user with USD currency uses USD price configuration', function () {
    $user = actingAsVerifiedUser(['currency' => Currency::USD]);

    expect($user->currency)->toBe(Currency::USD);

    $response = $this->post(route('subscription.checkout', ['plan' => 'monthly']));

    $response->assertRedirect();
});

test('swap route aborts 404 for invalid plan', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.swap', ['plan' => 'unknown']));

    $response->assertNotFound();
});

test('user without active subscription swapping plan handles gracefully', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.swap', ['plan' => 'yearly']));

    $response->assertRedirect();
});

test('user canceling non-existent subscription handles gracefully', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.cancel'));

    $response->assertRedirect();
});

test('user resuming non-existent subscription handles gracefully', function () {
    actingAsVerifiedUser();

    $response = $this->post(route('subscription.resume'));

    $response->assertRedirect();
});

test('user on yearly plan cannot swap directly to monthly plan', function () {
    $user = actingAsVerifiedUser();

    // Create subscription mock
    Mockery::mock(Subscription::class);

    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('subscribed')->andReturn(true);
    $userMock->shouldReceive('isYearlySubscribed')->andReturn(true);

    $response = $this->actingAs($userMock)->post(route('subscription.swap', ['plan' => 'monthly']));

    $response->assertSessionHas('error');
});
