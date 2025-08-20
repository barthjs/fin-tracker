<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    private static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'username' => $this->faker->unique()->userName,
            'email' => $this->faker->unique()->safeEmail(),
            'avatar' => $this->faker->imageUrl(),
            'password' => self::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
            'is_verified' => false,
            'is_admin' => false,
        ];
    }
}
