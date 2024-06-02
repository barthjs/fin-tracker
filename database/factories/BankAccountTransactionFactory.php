<?php

namespace Database\Factories;

use App\Models\BankAccountTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BankAccountTransaction>
 */
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
            'date' => fake()->date(),
            'amount' => $this->faker->randomFloat(2, 0, 1000000),
            'destination' => fake()->company(),
            'notes' => fake()->text(),
        ];
    }
}
