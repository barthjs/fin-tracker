<?php

declare(strict_types=1);

use App\Models\NotificationAssignment;
use App\Models\NotificationTarget;
use App\Models\User;

beforeEach(fn () => asUser());

it('return the assigned notification assignments', function (): void {
    $notificationAssignment = NotificationAssignment::factory()
        ->for(NotificationTarget::factory()->create(), 'target')
        ->create([
            'notifiable_type' => User::class,
            'notifiable_id' => auth()->id(),
        ]);

    expect($notificationAssignment->target->assignments->pluck('id'))->toContain($notificationAssignment->id);
});
