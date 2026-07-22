<?php

namespace App\Models;

use App\Enums\BudgetType;
use App\Enums\Currency;
use Carbon\Carbon;
use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property float $amount
 * @property Currency $currency
 * @property BudgetType $type
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @mixin Builder
 * @mixin Model
 */
#[Fillable(['user_id', 'name', 'amount', 'currency', 'type', 'description'])]
class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'currency' => Currency::class,
            'type' => BudgetType::class,
        ];
    }

    /**
     * Get formatted amount with currency symbol and space.
     */
    public function formattedAmount(): string
    {
        $currencyEnum = $this->currency instanceof Currency
            ? $this->currency
            : Currency::from($this->currency ?? Currency::EUR->value);

        return number_format((float) $this->amount, 2).' '.$currencyEnum->symbol();
    }

    /**
     * The user who owns this budget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
