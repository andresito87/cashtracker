<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use Carbon\Carbon;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $amount
 * @property ExpenseCategory|null $category
 * @property int $budget_id
 * @property Budget $budget
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @mixin Builder
 * @mixin Model
 */
#[Fillable(['name', 'amount', 'category', 'budget_id'])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
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
            'category' => ExpenseCategory::class,
        ];
    }

    /**
     * Get the human-readable category label.
     */
    public function categoryLabel(): string
    {
        return $this->category?->label() ?? 'Gastos Varios';
    }

    /**
     * Get the budget that owns the expense.
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }
}
