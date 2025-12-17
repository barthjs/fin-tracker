<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SecurityType;
use App\Models\Security;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Security>
 */
final class SecurityFactory extends Factory
{
    protected $model = Security::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'isin' => mb_strtoupper(fake()->lexify('??')).fake()->numerify('##########'),
            'symbol' => mb_strtoupper(fake()->lexify('???')),
            'type' => fake()->randomElement(SecurityType::cases()),
            'price' => fake()->randomFloat(6, 1, 100),
            'description' => fake()->text(20),
            'color' => fake()->hexColor(),
            'is_active' => true,
        ];
    }

    public function inActive(): self
    {
        return $this->state(['is_active' => true]);
    }
}
