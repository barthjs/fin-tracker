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
            'isin' => chr(rand(65, 90)).chr(rand(65, 90)).str_pad((string) rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'symbol' => strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, rand(3, 5))),
            'price' => $this->faker->randomFloat(3, 1, 100),
            'description' => $this->faker->text(20),
            'type' => $this->faker->randomElement(SecurityType::cases())->name,
        ];
    }
}
