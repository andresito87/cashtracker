<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureStripeWebhookSecret;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Always enforce signature verification instead of only when a secret is
     * configured. {@see EnsureStripeWebhookSecret} fails closed when the
     * signing secret is missing, and {@see VerifyWebhookSignature} rejects
     * invalid signatures and out-of-tolerance timestamps.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware(EnsureStripeWebhookSecret::class);
        $this->middleware(VerifyWebhookSignature::class);
    }
}
