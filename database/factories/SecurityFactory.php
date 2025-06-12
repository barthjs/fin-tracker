<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SecurityType;
use App\Models\Security;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecurityFactory extends Factory
{
    protected $model = Security::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'isin' => chr(mt_rand(65, 90)).chr(mt_rand(65, 90)).mb_str_pad((string) mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'symbol' => implode('', array_map(function () {
                return chr(mt_rand(65, 90)); // ASCII A–Z
            }, range(0, mt_rand(2, 4)))),
            'price' => $this->faker->randomFloat(3, 1, 100),
            'description' => $this->faker->text(20),
            'type' => $this->faker->randomElement(SecurityType::cases())->name,
        ];
    }
}
