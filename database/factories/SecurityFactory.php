<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Country;
use App\Enums\SecurityRegion;
use App\Enums\SecuritySector;
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
            'region' => fake()->optional(0.7)->randomElement(SecurityRegion::cases()),
            'sector' => fake()->optional(0.7)->randomElement(SecuritySector::cases()),
            'country' => fake()->optional(0.7)->randomElement(Country::cases()),
            'exchange' => fake()->optional(0.5)->randomElement(['XETRA', 'NYSE', 'NASDAQ', 'LSE', 'SIX']),
            'ter' => fake()->optional(0.5)->randomFloat(2, 0.05, 1.5),
            'website' => fake()->optional(0.5)->url(),
        ];
    }

    public function inActive(): self
    {
        return $this->state(['is_active' => true]);
    }
}
