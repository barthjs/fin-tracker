<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationProviderType;
use App\Models\NotificationTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTarget>
 */
final class NotificationTargetFactory extends Factory
{
    protected $model = NotificationTarget::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'type' => fake()->randomElement(NotificationProviderType::cases()),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
