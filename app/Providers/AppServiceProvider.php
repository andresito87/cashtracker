<?php

namespace App\Providers;

use App\Http\Controllers\StripeWebhookController;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Allows past due subscriptions to remain active for a grace period.
        Cashier::keepPastDueSubscriptionsActive();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force signature verification on Cashier's webhook (fail-closed) by
        // swapping its controller through the container, without re-registering routes.
        $this->app->bind(CashierWebhookController::class, function () {
            return new StripeWebhookController;
        });

        // Admins bypass all policy checks automatically.
        // This means every Gate::authorize() call in controllers
        // will return true for admins without reaching the Policy class.
        Gate::before(function (User $user) {
            if ($user->isAdmin()) {
                return true;
            }

            return null;
        });
    }
}
