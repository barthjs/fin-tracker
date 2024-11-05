<?php

namespace Database\Factories;

use App\Models\CategoryStatistic;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryStatisticFactory extends Factory
{
    protected $model = CategoryStatistic::class;

    public function definition(): array
    {
        return [
            'year' => fake()->year,
            'jan' => fake()->numberBetween(-1000, 1000),
            'feb' => fake()->numberBetween(-1000, 1000),
            'mar' => fake()->numberBetween(-1000, 1000),
            'apr' => fake()->numberBetween(-1000, 1000),
            'may' => fake()->numberBetween(-1000, 1000),
            'jun' => fake()->numberBetween(-1000, 1000),
            'jul' => fake()->numberBetween(-1000, 1000),
            'aug' => fake()->numberBetween(-1000, 1000),
            'sep' => fake()->numberBetween(-1000, 1000),
            'oct' => fake()->numberBetween(-1000, 1000),
            'nov' => fake()->numberBetween(-1000, 1000),
            'dec' => fake()->numberBetween(-1000, 1000),
        ];
    }
}
