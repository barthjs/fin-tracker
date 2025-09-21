<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Currency;
use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Portfolio>
 */
final class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

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
            'is_active' => fake()->boolean(),
        ];
    }
}
