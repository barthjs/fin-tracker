<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PeriodUnit;
use App\Models\Budget;
use App\Models\Category;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
final class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'period_unit' => PeriodUnit::Month,
            'period_frequency' => 1,
            'starts_at' => CarbonImmutable::now()->startOfMonth(),
            'ends_at' => null,
            'warning_threshold' => null,
            'notify_on_exceed' => false,
            'is_active' => true,
        ];
    }

    /**
     * A budget that sends alert notifications.
     */
    public function notifying(): self
    {
        return $this->state(['notify_on_exceed' => true]);
    }
}
