<?php

declare(strict_types=1);

namespace App\Services\Notifications\Configs;

final readonly class EmailConfig implements NotificationConfig
{
    public function __construct(
        public string $email,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: isset($data['email']) && is_string($data['email']) ? $data['email'] : '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
        ];
    }
}
