<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Throwable;

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
        $user = $request->user();
        $subscription = $user?->subscription();
        $subscribed = $user ? $user->subscribed() : false;

        $nextBillingDate = null;
        if ($subscription) {
            if ($subscription->ends_at) {
                $nextBillingDate = $subscription->ends_at->format('Y-m-d');
            } else {
                $nextBillingDate = Cache::remember(
                    "next_billing_date.$user->id",
                    now()->addHour(),
                    function () use ($subscription, $user) {
                        try {
                            $periodEnd = $subscription->asStripeSubscription()->current_period_end;

                            return Carbon::createFromTimestamp($periodEnd)->format('Y-m-d');
                        } catch (Throwable) {
                            $isYearly = $user->isYearlySubscribed();

                            return ($subscription->created_at ?? now())->{$isYearly ? 'addYear' : 'addMonth'}()->format('Y-m-d');
                        }
                    }
                );
            }
        }

        // Return shared data to Inertia, including authenticated user info, flash messages, translations, and locale.
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_admin' => $user->isAdmin(),
                    'currency' => $user->currency?->value ?? 'EUR',
                    'currency_symbol' => $user->currency?->symbol() ?? '€',
                    'subscribed' => $subscribed,
                    'on_grace_period' => $subscription?->onGracePeriod() ?? false,
                    'ends_at' => $nextBillingDate,
                    'plan' => $subscribed ? ($user->isYearlySubscribed() ? 'yearly' : 'monthly') : null,
                ] : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status')
                    ?? $request->session()->get('success')
                    ?? $request->session()->get('error'),
                'status_type' => fn () => $request->session()->has('error')
                    ? 'error'
                    : ($request->session()->get('status_type') ?? 'success'),
            ],
            'translations' => [
                'messages' => __('messages'),
            ],
            'locale' => app()->getLocale(),
            'available_locales' => config('app.available_locales'),
            'default_locale' => config('app.locale'),
        ];
    }
}
