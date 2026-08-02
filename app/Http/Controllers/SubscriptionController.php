<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Exceptions\SubscriptionUpdateFailure;
use Laravel\Cashier\Subscription;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SubscriptionController extends Controller
{
    /**
     * Create a new subscription checkout session.
     *
     * @return Response
     *
     * @throws Exception
     */
    public function checkout(Request $request, string $plan)
    {
        $user = $request->user();
        $currency = $user->currency?->value ?? 'EUR';

        $priceId = config("services.stripe.prices.$currency.$plan")
            ?? config("services.stripe.price_ai_$plan");

        abort_unless($priceId, 404, __('messages.invalid_plan'));

        $checkout = $user
            ->newSubscription('default', $priceId)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.success'),
                'cancel_url' => route('billing.cancel'),
            ]);

        return Inertia::location($checkout->redirect()->getTargetUrl());
    }

    /**
     * Display the subscription management page.
     *
     * @return \Inertia\Response
     */
    public function manage(Request $request)
    {
        $user = $request->user();
        $subscription = $user->subscription();

        $nextBillingDate = null;
        if ($subscription) {
            $endsAt = $subscription->getAttribute('ends_at');
            if ($endsAt) {
                $nextBillingDate = $endsAt->format('Y-m-d');
            } else {
                try {
                    $periodEndStr = $this->getNextBillingDate($subscription);
                    $nextBillingDate = $periodEndStr ? Carbon::parse($periodEndStr)->format('Y-m-d') : null;
                } catch (Throwable) {
                    $isYearly = $user->isYearlySubscribed();
                    $createdAt = $subscription->getAttribute('created_at') ?? now();
                    $nextBillingDate = ($isYearly ? $createdAt->addYear() : $createdAt->addMonth())->format('Y-m-d');
                }
            }
        }

        return Inertia::render('Subscriptions/Manage', [
            'subscription' => $subscription ? [
                'stripe_status' => (string) $subscription->getAttribute('stripe_status'),
                'ends_at' => $nextBillingDate,
                'next_billing_date' => $nextBillingDate,
                'on_grace_period' => $subscription->onGracePeriod(),
                'canceled' => $subscription->canceled(),
                'status_label' => $this->buildStatusLabel($subscription, $nextBillingDate),
            ] : null,
        ]);
    }

    /**
     * Switch the subscription plan.
     *
     * @return RedirectResponse
     *
     * @throws IncompletePayment
     * @throws SubscriptionUpdateFailure
     */
    public function swap(Request $request, string $plan)
    {
        $user = $request->user();
        $currency = $user->currency?->value ?? 'EUR';

        $priceId = config("services.stripe.prices.$currency.$plan")
            ?? config("services.stripe.price_ai_$plan");

        abort_unless($priceId, 404, __('messages.invalid_plan'));

        if ($user->subscribed()) {
            if ($plan === 'monthly' && $user->isYearlySubscribed()) {
                return back()->with('error', __('messages.cannot_downgrade_yearly'));
            }

            $currentSub = $user->subscription();
            if ($currentSub) {
                if ($currentSub->onGracePeriod()) {
                    $currentSub->resume();
                }

                $currentSub->swap($priceId);
                cache()->forget("stripe.next_billing.{$currentSub->getKey()}");
            }
        }

        return back()->with('status', __('messages.subscription_swapped'));
    }

    /**
     * Cancel the subscription.
     *
     * @return RedirectResponse
     */
    public function cancel(Request $request)
    {
        $user = $request->user();

        if ($user->subscribed()) {
            $currentSub = $user->subscription();
            if ($currentSub) {
                $currentSub->cancel();
                cache()->forget("stripe.next_billing.{$currentSub->getKey()}");
            }
        }

        return back()->with('status', __('messages.subscription_canceled_success'));
    }

    /**
     * Resume the subscription.
     *
     * @return RedirectResponse
     */
    public function resume(Request $request)
    {
        $user = $request->user();
        $currentSub = $user->subscription();

        if ($currentSub?->onGracePeriod()) {
            $currentSub->resume();
            cache()->forget("stripe.next_billing.{$currentSub->getKey()}");
        }

        return back()->with('status', __('messages.subscription_resumed_success'));
    }

    /**
     * Get the next billing date for the subscription.
     */
    private function getNextBillingDate(Subscription $subscription): ?string
    {
        $subscriptionId = $subscription->getKey();

        // Use caching to avoid frequent API calls to Stripe for the next billing date
        return cache()->remember(
            "stripe.next_billing.$subscriptionId",
            now()->addMinutes(15),
            function () use ($subscription, $subscriptionId) {
                try {
                    $stripe = $subscription->asStripeSubscription();
                    $periodEnd = $stripe->current_period_end ?? null;
                    if (! $periodEnd && isset($stripe->items->data[0])) {
                        $periodEnd = $stripe->items->data[0]->current_period_end ?? null;
                    }

                    return $periodEnd
                        ? Carbon::createFromTimestamp($periodEnd)->toIso8601String()
                        : null;
                } catch (Exception $e) {
                    logger()->error('Error obteniendo next billing date', [
                        'error' => $e->getMessage(),
                        'subscription_id' => $subscriptionId,
                    ]);

                    return null;
                }
            }
        );
    }

    /**
     * Build the status label for the subscription.
     */
    private function buildStatusLabel(Subscription $subscription, ?string $nextBillingDate): array
    {
        $endsAt = $subscription->getAttribute('ends_at');

        if ($subscription->ended()) {
            return [
                'text' => __('messages.status_ended_title'),
                'description' => __('messages.status_ended_desc'),
                'date' => $endsAt?->toIso8601String(),
                'color' => 'gray',
            ];
        }

        if ($subscription->onGracePeriod()) {
            return [
                'text' => __('messages.status_canceled_title'),
                'description' => __('messages.status_canceled_desc'),
                'date' => $endsAt?->toIso8601String(),
                'color' => 'orange',
            ];
        }

        // If the subscription has an incomplete payment or is past due, check the latest invoice
        if ($subscription->hasIncompletePayment() || $subscription->pastDue()) {
            // If the latest invoice is paid, the subscription is active, he did a swap or a resume, so we can show it as active
            if ($this->latestInvoiceIsPaid($subscription)) {
                return [
                    'text' => __('messages.status_active_title'),
                    'description' => __('messages.status_active_desc'),
                    'color' => 'green',
                    'date' => $nextBillingDate,
                ];
            }

            if ($subscription->hasIncompletePayment()) {
                return [
                    'text' => __('messages.status_incomplete_title'),
                    'description' => __('messages.status_incomplete_desc'),
                    'date' => null,
                    'color' => 'red',
                ];
            }

            return [
                'text' => __('messages.status_past_due_title'),
                'description' => __('messages.status_past_due_desc'),
                'date' => null,
                'color' => 'red',
            ];
        }

        return [
            'text' => __('messages.status_active_title'),
            'description' => __('messages.status_active_desc'),
            'color' => 'green',
            'date' => $nextBillingDate,
        ];
    }

    /**
     * Check if the latest invoice for the subscription is paid.
     */
    private function latestInvoiceIsPaid(Subscription $subscription): bool
    {
        $subscriptionId = $subscription->getKey();

        try {
            $stripeSub = $subscription->asStripeSubscription();
            $latestInvoiceId = $stripeSub->latest_invoice;

            if (! $latestInvoiceId) {
                return false;
            }

            $invoiceId = is_string($latestInvoiceId) ? $latestInvoiceId : ($latestInvoiceId->id ?? null);
            if (! $invoiceId) {
                return false;
            }

            $invoice = Cashier::stripe()
                ->invoices
                ->retrieve($invoiceId);

            return $invoice->status === 'paid';

        } catch (Exception $e) {
            logger()->error('Error verificando invoice', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId,
            ]);

            return false;
        }
    }

    /**
     * Redirect to the Stripe Customer Billing Portal.
     */
    public function billing(Request $request): Response|RedirectResponse
    {
        return $request->user()->redirectToBillingPortal(route('subscription.manage'));
    }

    /**
     * Handle successful billing checkout redirect.
     */
    public function success(): RedirectResponse
    {
        return redirect()->route('subscription.manage')
            ->with('status', __('messages.billing_success_title'));
    }

    /**
     * Handle canceled billing checkout redirect.
     */
    public function cancelUrl(): RedirectResponse
    {
        return redirect()->route('subscription.manage')
            ->with('error', __('messages.billing_cancel_title'));
    }
}
