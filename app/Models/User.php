<?php

namespace App\Models;

use App\Enums\Currency;
use App\Notifications\VerifyEmail;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Currency $currency
 * @property string|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string|null $remember_token
 * @property Collection<int, Budget> $budgets
 * @property Collection<int, Expense> $expenses
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Builder
 * @mixin Model
 */
#[Fillable(['name', 'email', 'password', 'role', 'currency'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable;

    public function sendEmailVerificationNotification(): void
    {
        // When event Registered is triggered, this method will be called to send the email verification notification
        $this->notify(new VerifyEmail);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'currency' => Currency::class,
        ];
    }

    /**
     * Check if the user has the admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user has the regular user role.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * All budgets belonging to this user.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * All expenses belonging to this user through their budgets.
     */
    public function expenses(): HasManyThrough
    {
        return $this->hasManyThrough(Expense::class, Budget::class);
    }

    private function currentPlan(): ?string
    {
        $subscription = $this->subscription();

        if ($subscription && $subscription->active()) {
            return $subscription->stripe_price;
        }

        return null;
    }

    public function isMonthlySubscribed(): bool
    {
        $price = $this->currentPlan();
        if (! $price) {
            return false;
        }

        $monthlyPrices = array_filter([
            config('services.stripe.price_ai_monthly'),
            config('services.stripe.prices.EUR.monthly'),
            config('services.stripe.prices.USD.monthly'),
        ]);

        return in_array($price, $monthlyPrices, true);
    }

    public function isYearlySubscribed(): bool
    {
        $price = $this->currentPlan();
        if (! $price) {
            return false;
        }

        $yearlyPrices = array_filter([
            config('services.stripe.price_ai_yearly'),
            config('services.stripe.prices.EUR.yearly'),
            config('services.stripe.prices.USD.yearly'),
        ]);

        return in_array($price, $yearlyPrices, true);
    }
}
