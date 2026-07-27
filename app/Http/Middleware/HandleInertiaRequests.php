<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'layouts.inertia';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Return shared data to Inertia, including authenticated user info, flash messages, translations, and locale.
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'currency' => $request->user()->currency?->value,
                    'currency_symbol' => $request->user()->currency?->symbol() ?? '€',
                ] : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'status_type' => fn () => $request->session()->get('status_type'),
            ],
            'translations' => [
                'messages' => __('messages'),
            ],
            'locale' => app()->getLocale(),
        ];
    }
}
