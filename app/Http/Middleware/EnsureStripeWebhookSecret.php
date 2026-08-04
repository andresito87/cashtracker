<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureStripeWebhookSecret
{
    /**
     * Reject webhook traffic whenever the Stripe webhook signing secret is
     * not configured. Cashier only applies signature verification when a
     * secret exists, which would otherwise leave the endpoint wide open.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (blank(config('cashier.webhook.secret'))) {
            throw new AccessDeniedHttpException('Stripe webhook secret is not configured.');
        }

        return $next($request);
    }
}
