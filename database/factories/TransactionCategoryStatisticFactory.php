<?php

namespace Database\Factories;

use App\Models\TransactionCategoryStatistic;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionCategoryStatisticFactory extends Factory
{
    protected $model = TransactionCategoryStatistic::class;

    public function definition(): array
    {
        return [
            'year' => fake()->year,
            'amount' => fake()->randomFloat(2, 0, 10000),
            'jan' => fake()->randomFloat(2, 0, 10000),
            'feb' => fake()->randomFloat(2, 0, 10000),
            'mar' => fake()->randomFloat(2, 0, 10000),
            'apr' => fake()->randomFloat(2, 0, 10000),
            'may' => fake()->randomFloat(2, 0, 10000),
            'jun' => fake()->randomFloat(2, 0, 10000),
            'jul' => fake()->randomFloat(2, 0, 10000),
            'aug' => fake()->randomFloat(2, 0, 10000),
            'sep' => fake()->randomFloat(2, 0, 10000),
            'oct' => fake()->randomFloat(2, 0, 10000),
            'nov' => fake()->randomFloat(2, 0, 10000),
            'dec' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
