<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationAssignment>
 */
final class NotificationAssignmentFactory extends Factory
{
    protected $model = NotificationAssignment::class;

    public function definition(): array
    {
        return [];
    }
}
