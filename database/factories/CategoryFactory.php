<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryGroup;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
final class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $group = fake()
            ->randomElement([
                CategoryGroup::FixExpenses,
                CategoryGroup::VarExpenses,
                CategoryGroup::FixRevenues,
                CategoryGroup::VarRevenues,
            ]);

        return [
            'name' => fake()->word(),
            'group' => $group,
            'color' => fake()->hexColor(),
            'is_active' => fake()->boolean(),
        ];
    }
}
