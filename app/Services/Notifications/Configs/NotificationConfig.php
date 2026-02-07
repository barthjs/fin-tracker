<?php

declare(strict_types=1);

namespace App\Services\Notifications\Configs;

interface NotificationConfig
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
