<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationProviderType;
use App\Services\Notifications\Strategies\DatabaseStrategy;
use App\Services\Notifications\Strategies\GenericWebhookStrategy;
use App\Services\Notifications\Strategies\NotificationSenderStrategy;

final readonly class NotificationStrategyFactory
{
    /**
     * Choose the correct notification strategy for the given provider type
     */
    public function make(NotificationProviderType $type): NotificationSenderStrategy
    {
        return match ($type) {
            NotificationProviderType::DATABASE => new DatabaseStrategy(),
            NotificationProviderType::GENERIC_WEBHOOK => new GenericWebhookStrategy(),
        };
    }
}
