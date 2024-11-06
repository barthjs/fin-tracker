<?php

namespace Database\Factories;

use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'market_value' => $this->faker->randomFloat(2, 1, 10000),
            'description' => $this->faker->sentence(),
        ];
    }
}
