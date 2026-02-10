<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationEventType: string
{
    case SUBSCRIPTION_REMINDER = 'subscription_reminder';
}
