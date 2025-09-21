<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CategoryStatistic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryStatistic>
 */
final class CategoryStatisticFactory extends Factory
{
    protected $model = CategoryStatistic::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => fake()->year,
            'jan' => fake()->randomFloat(2, 0, 1000),
            'feb' => fake()->randomFloat(2, 0, 1000),
            'mar' => fake()->randomFloat(2, 0, 1000),
            'apr' => fake()->randomFloat(2, 0, 1000),
            'may' => fake()->randomFloat(2, 0, 1000),
            'jun' => fake()->randomFloat(2, 0, 1000),
            'jul' => fake()->randomFloat(2, 0, 1000),
            'aug' => fake()->randomFloat(2, 0, 1000),
            'sep' => fake()->randomFloat(2, 0, 1000),
            'oct' => fake()->randomFloat(2, 0, 1000),
            'nov' => fake()->randomFloat(2, 0, 1000),
            'dec' => fake()->randomFloat(2, 0, 1000),
        ];
    }
}
