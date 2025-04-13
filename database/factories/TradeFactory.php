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
        return [
            'date_time' => Carbon::now(),
            'notes' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(TradeType::cases())->name,
        ];
    }
}
