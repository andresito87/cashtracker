<?php

use App\Http\Middleware\Subscribed;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

test('unsubscribed user is redirected to subscription.manage route', function () {
    $user = actingAsVerifiedUser();

    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('subscribed')->andReturn(false);

    $this->actingAs($userMock);

    $middleware = new Subscribed;
    $request = Request::create('/test-protected', 'GET');

    $response = $middleware->handle($request, function () {
        return new Response('OK');
    });

    expect($response->isRedirect(route('subscription.manage')))->toBeTrue();
    expect(session('error'))->toBe('You must be subscribed to access this feature.');
});

test('subscribed user can pass through middleware', function () {
    $user = actingAsVerifiedUser();

    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('subscribed')->andReturn(true);

    $this->actingAs($userMock);

    $middleware = new Subscribed;
    $request = Request::create('/test-protected', 'GET');

    $response = $middleware->handle($request, function () {
        return new Response('OK');
    });

    expect($response->getContent())->toBe('OK');
});
