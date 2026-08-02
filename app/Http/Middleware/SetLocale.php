<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $available = config('app.available_locales') ?? [];
        $locale = null;

        // 1. Check if locale is explicitly passed in the URL query string (?lang=es|en)
        if ($request->has('lang')) {
            $queryLocale = $request->query('lang');
            if (in_array($queryLocale, $available)) {
                $locale = $queryLocale;
                if ($request->hasSession()) {
                    session(['locale' => $queryLocale]);
                }
            }
        }

        // 2. Check if a previously selected locale exists in the user session
        if (! $locale && $request->hasSession()) {
            $locale = session('locale');
        }

        // 3. Fallback to default application locale if no valid locale was found
        if (! in_array($locale, $available)) {
            $locale = config('app.locale', 'es');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
