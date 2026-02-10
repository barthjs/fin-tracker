<?php

declare(strict_types=1);

namespace App\Services\Notifications\Strategies;

use App\Data\NotificationPayload;
use App\Models\NotificationTarget;

interface NotificationSenderStrategy
{
    public function send(NotificationTarget $target, NotificationPayload $payload): void;
}
