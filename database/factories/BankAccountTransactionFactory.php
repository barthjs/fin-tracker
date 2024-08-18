<?php

namespace Database\Factories;

use App\Models\BankAccountTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountTransactionFactory extends Factory
{
    protected $model = BankAccountTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => fake()->dateTime(),
            'amount' => fake()->randomFloat(2, -10000, 10000),
            'destination' => fake()->company(),
            'notes' => fake()->sentence(),
        ];
    }
}
