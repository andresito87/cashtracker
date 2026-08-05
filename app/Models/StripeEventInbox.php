<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $stripe_event_id
 * @property string $stripe_event_type
 * @property array $payload
 * @property Carbon|null $processed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Builder
 * @mixin Model
 */
class StripeEventInbox extends Model
{
    protected $table = 'stripe_event_inbox';

    protected $fillable = [
        'stripe_event_id',
        'stripe_event_type',
        'payload',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
