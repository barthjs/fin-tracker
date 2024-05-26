<?php

namespace Database\Factories;

use App\Models\BankAccount;
use App\Models\BankAccountTransaction;
use App\Models\TransactionCategory;
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
        $bankAccount = BankAccount::all()->random();
        $categoryId = TransactionCategory::whereUserId($bankAccount->user_id)->get(['category_id'])->random();
        return [
            'date' => fake()->date(),
            'amount' => $this->faker->randomFloat(2, 0, 1000000),
            'destination' => fake()->company(),
            'notes' => fake()->text(),

            'bank_account_id' => $bankAccount->bank_account_id,
            'category_id' => $categoryId,
        ];
    }
}
