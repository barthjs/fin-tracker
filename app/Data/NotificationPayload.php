<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class NotificationPayload implements Arrayable
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $title,
        public string $body,
        public array $metadata = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            ...($this->metadata ? ['meta' => $this->metadata] : []),
            'timestamp' => now()->toIso8601String(),
            'version' => config('app.version'),
        ];
    }
}
