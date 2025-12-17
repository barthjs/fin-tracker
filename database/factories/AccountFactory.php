<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
final class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'currency' => Currency::getCurrency(),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'is_active' => true,
        ];
    }

    public function inActive(): self
    {
        return $this->state(['is_active' => true]);
    }
}
