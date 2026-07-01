<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationEventType: string
{
    case ACCOUNT_LOW_BALANCE = 'account_low_balance';
    case BUDGET_ALERT = 'budget_alert';
    case SUBSCRIPTION_REMINDER = 'subscription_reminder';
}
