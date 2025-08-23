<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
final class TransactionFactory extends Factory
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
            'transaction_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'type' => fake()->randomElement([TransactionType::Expense, TransactionType::Revenue]),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'payee' => fake()->company(),
            'notes' => fake()->sentence(),
        ];
    }
}
