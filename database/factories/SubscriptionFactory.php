<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PeriodUnit;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
final class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->realText(),
            'amount' => fake()->randomFloat(),
            'period_unit' => fake()->randomElement(PeriodUnit::cases()),
            'period_frequency' => fake()->numberBetween(1, 365),
            'started_at' => CarbonImmutable::now(),
            'color' => fake()->hexColor(),
            'is_active' => true,
        ];
    }

    public function inActive(): self
    {
        return $this->state(['is_active' => true]);
    }
}
