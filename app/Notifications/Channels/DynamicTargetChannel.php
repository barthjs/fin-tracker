<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Contracts\HasDynamicNotificationTarget;
use App\Services\Notifications\NotificationStrategyFactory;
use Illuminate\Notifications\Notification;

final readonly class DynamicTargetChannel
{
    public function __construct(
        private NotificationStrategyFactory $strategyFactory
    ) {}

    public function send(mixed $notifiable, Notification&HasDynamicNotificationTarget $notification): void
    {
        $target = $notification->getNotificationTarget();

        if (! $target->is_active) {
            return;
        }

        $payload = $notification->toNotificationPayload();

        $this->strategyFactory->make($target->type)->send($target, $payload);
    }
}
