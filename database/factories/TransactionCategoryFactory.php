<?php

namespace Database\Factories;

use App\Enums\TransactionGroup;
use App\Models\TransactionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionCategoryFactory extends Factory
{
    protected $model = TransactionCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $group = fake()->randomElement(TransactionGroup::cases())->name;
        return [
            'name' => fake()->word(),
            'group' => $group,
        ];
    }
}
