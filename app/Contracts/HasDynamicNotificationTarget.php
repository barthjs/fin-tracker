<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\NotificationPayload;
use App\Models\NotificationTarget;

interface HasDynamicNotificationTarget
{
    public function getNotificationTarget(): NotificationTarget;

    public function toNotificationPayload(): NotificationPayload;
}
