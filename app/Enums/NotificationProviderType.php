<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NotificationProviderType: string implements HasColor, HasIcon, HasLabel
{
    case DATABASE = 'database';
    case GENERIC_WEBHOOK = 'generic_webhook';

    public function getColor(): string
    {
        return match ($this) {
            self::DATABASE => 'info',
            self::GENERIC_WEBHOOK => 'success',
        };
    }

    public function getLabel(): string
    {
        return __('notification_target.configuration.'.$this->value.'.label');
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::DATABASE => 'tabler-database',
            self::GENERIC_WEBHOOK => 'tabler-webhook',
        };
    }
}
