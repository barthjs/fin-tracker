<?php

declare(strict_types=1);

namespace App\Services\Notifications\Configs;

final readonly class DatabaseConfig implements NotificationConfig
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self;
    }

    public function toArray(): array
    {
        return [];
    }
}
