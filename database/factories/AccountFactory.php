<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
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
            'name' => fake()->unique()->word(),
            'balance' => fake()->randomFloat(2, 0, 1000000),
            'currency' => Account::getCurrency(),
            'description' => fake()->sentence(),
        ];
    }
}
