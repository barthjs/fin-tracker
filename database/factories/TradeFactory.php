<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TradeType;
use App\Models\Trade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trade>
 */
final class TradeFactory extends Factory
{
    protected $model = Trade::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trade_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'type' => fake()->randomElement(TradeType::cases()),
            'price' => fake()->randomFloat(2, 1, 100),
            'fee' => fake()->randomFloat(2, 0, 10),
            'tax' => fake()->randomFloat(2, 0, 10),
            'notes' => fake()->sentence(),
        ];
    }
}
