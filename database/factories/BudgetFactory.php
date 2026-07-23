<?php

namespace Database\Factories;

use App\Enums\BudgetType;
use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'type' => BudgetType::General,
            'description' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the budget is a goal budget.
     */
    public function goal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BudgetType::Goal,
        ]);
    }

    /**
     * Indicate that the budget is a general budget.
     */
    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BudgetType::General,
        ]);
    }
}
