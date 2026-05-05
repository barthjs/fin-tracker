<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UserProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserProvider>
 */
final class UserProviderFactory extends Factory
{
    protected $model = UserProvider::class;

    public function definition(): array
    {
        return [];
    }
}
