<?php

namespace Database\Factories;

use App\Models\SecurityType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecurityTypeFactory extends Factory
{
    protected $model = SecurityType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
