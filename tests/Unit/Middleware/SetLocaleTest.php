<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

it('stores supported locales from the query string in session', function () {
    $middleware = new SetLocale;
    $request = Request::create('/?lang=es');
    $request->setLaravelSession(app('session.store'));

    $middleware->handle($request, fn () => response('ok'));

    expect(session('locale'))->toBe('es')
        ->and(app()->getLocale())->toBe('es');
});

it('ignores unsupported locales from the query string', function () {
    $middleware = new SetLocale;
    $request = Request::create('/?lang=fr');
    $request->setLaravelSession(app('session.store'));

    $middleware->handle($request, fn () => response('ok'));

    expect(session('locale'))->toBeNull()
        ->and(app()->getLocale())->toBe(config('app.locale'));
});

it('applies the locale stored in session', function () {
    session(['locale' => 'es']);

    $middleware = new SetLocale;
    $request = Request::create('/');
    $request->setLaravelSession(app('session.store'));

    $middleware->handle($request, fn () => response('ok'));

    expect(app()->getLocale())->toBe('es');
});

it('accepts locales added to config(app.available_locales)', function () {
    config()->set('app.available_locales', ['en', 'es', 'fr']);

    $middleware = new SetLocale;
    $request = Request::create('/?lang=fr');
    $request->setLaravelSession(app('session.store'));

    $middleware->handle($request, fn () => response('ok'));

    expect(session('locale'))->toBe('fr')
        ->and(app()->getLocale())->toBe('fr');
});

it('rejects locales not present in config(app.available_locales)', function () {
    config()->set('app.available_locales', ['en', 'es']);

    $middleware = new SetLocale;
    $request = Request::create('/?lang=de');
    $request->setLaravelSession(app('session.store'));

    $middleware->handle($request, fn () => response('ok'));

    expect(session('locale'))->toBeNull()
        ->and(app()->getLocale())->toBe(config('app.locale'));
});
