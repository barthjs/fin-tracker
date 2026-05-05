<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PersonalAccessToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonalAccessToken>
 */
final class PersonalAccessTokenFactory extends Factory
{
    protected $model = PersonalAccessToken::class;

    public function definition(): array
    {
        return [];
    }
}
