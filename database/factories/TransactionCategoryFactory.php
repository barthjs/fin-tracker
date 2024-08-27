<?php

namespace Database\Factories;

use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
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
        $type = match ($group) {
            "transfers" => TransactionType::transfer->name,
            "var_revenues", "fix_revenues" => TransactionType::revenue->name,
            default => TransactionType::expense->name,
        };

        return [
            'name' => fake()->word(),
            'type' => $type,
            'group' => $group,
        ];
    }
}
