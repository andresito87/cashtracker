<?php

use App\Http\Middleware\Subscribed;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;

test('unsubscribed user is redirected to subscription.manage route', function () {
    $user = actingAsVerifiedUser();

    /** @var User|MockInterface $userMock */
    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('subscribed')->andReturn(false);

    $this->be($userMock);

    $middleware = new Subscribed;
    $request = Request::create('/test-protected');

    $response = $middleware->handle($request, function () {
        return new Response('OK');
    });

    $targetUrl = route('subscription.manage');
    $isRedirect = $response->isRedirect($targetUrl);

    $this->assertTrue($isRedirect);
    expect(session('error'))->toBe('You must be subscribed to access this feature.');
});

test('subscribed user can pass through middleware', function () {
    $user = actingAsVerifiedUser();

    /** @var User|MockInterface $userMock */
    $userMock = Mockery::mock($user)->makePartial();
    $userMock->shouldReceive('subscribed')->andReturn(true);

    $this->be($userMock);

    $middleware = new Subscribed;
    $request = Request::create('/test-protected');

    $response = $middleware->handle($request, function () {
        return new Response('OK');
    });

    expect($response->getContent())->toBe('OK');
});
