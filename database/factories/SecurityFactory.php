<?php

namespace Database\Factories;

use App\Models\Security;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecurityFactory extends Factory
{
    protected $model = Security::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'isin' => chr(rand(65, 90)) . chr(rand(65, 90)) . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'symbol' => strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, rand(3, 5))),
            'price' => $this->faker->randomFloat(3, 1, 100),
            'total_quantity' => $this->faker->randomFloat(6, 1, 100),
            'description' => $this->faker->text(),
        ];
    }
}
