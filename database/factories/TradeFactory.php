<?php

namespace Database\Factories;

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
        $totalAmount = $price * $quantity + $tax + $fee;

        return [
            'date_time' => Carbon::now(),
            'total_amount' => $totalAmount,
            'quantity' => $quantity,
            'price' => $price,
            'tax' => $tax,
            'fee' => $fee,
            'notes' => $this->faker->words(3, true),
        ];
    }
}
