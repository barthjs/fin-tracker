<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date_time' => fake()->dateTime(),
            'amount' => fake()->numberBetween(-10000, 10000),
            'destination' => fake()->company(),
            'notes' => fake()->sentence(),
        ];
    }
}
