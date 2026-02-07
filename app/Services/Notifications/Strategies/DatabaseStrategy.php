<?php

declare(strict_types=1);

namespace App\Services\Notifications\Strategies;

use App\Data\NotificationPayload;
use App\Models\NotificationTarget;
use Filament\Notifications\Notification as FilamentNotification;

/**
 * Send notifications to the database.
 */
final readonly class DatabaseStrategy implements NotificationSenderStrategy
{
    public function send(NotificationTarget $target, NotificationPayload $payload): void
    {
        FilamentNotification::make()
            ->title($payload->title)
            ->body($payload->body)
            ->sendToDatabase($target->user);
    }
}
