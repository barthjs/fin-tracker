<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\NotificationTarget;

final class NotificationTargetObserver
{
    public function creating(NotificationTarget $notificationTarget): void
    {
        /** @phpstan-ignore-next-line */
        if ($notificationTarget->user_id === null) {
            $notificationTarget->user_id = auth()->user()->id;
        }
    }
}
