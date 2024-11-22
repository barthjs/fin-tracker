<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionGroup;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

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
