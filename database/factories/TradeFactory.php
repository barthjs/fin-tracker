<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TradeType;
use App\Models\Trade;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TradeFactory extends Factory
{
    protected $model = Trade::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $price = $this->faker->randomFloat(2, 1, 100);
        $tax = $this->faker->numberBetween(0, 100);
        $fee = $this->faker->numberBetween(0, 100);

        return [
            'date_time' => Carbon::now(),
            'quantity' => $quantity,
            'price' => $price,
            'tax' => $tax,
            'fee' => $fee,
            'notes' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(TradeType::cases())->name,
        ];
    }
}
