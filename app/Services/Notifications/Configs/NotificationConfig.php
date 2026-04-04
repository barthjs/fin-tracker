<?php

declare(strict_types=1);

namespace App\Services\Notifications\Configs;

interface NotificationConfig
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
