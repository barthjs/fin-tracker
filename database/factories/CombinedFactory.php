<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Combined;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Combined>
 */
final class CombinedFactory extends Factory
{
    protected $model = Combined::class;

    public function definition(): array
    {
        return [];
    }
}
