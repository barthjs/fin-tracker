<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationProviderType;
use App\Services\Notifications\Configs\DatabaseConfig;
use App\Services\Notifications\Configs\GenericWebhookConfig;
use App\Services\Notifications\Configs\NotificationConfig;

final readonly class NotificationConfigFactory
{
    /**
     * Choose the correct notification config for the given provider type
     *
     * @param  array<string, mixed>  $data
     */
    public static function make(NotificationProviderType $type, array $data): NotificationConfig
    {
        return match ($type) {
            NotificationProviderType::DATABASE => DatabaseConfig::fromArray($data),
            NotificationProviderType::GENERIC_WEBHOOK => GenericWebhookConfig::fromArray($data),
        };
    }
}
