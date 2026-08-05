<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Persistent checkout-attempt receipt for idempotent checkout.
 *
 * @property int $id
 * @property int $user_id
 * @property string $plan
 * @property string|null $idempotency_key
 * @property string $stripe_session_id
 * @property string $stripe_url
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Builder
 * @mixin Model
 */
class CheckoutAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'idempotency_key',
        'stripe_session_id',
        'stripe_url',
        'status',
    ];
}
