<?php

declare(strict_types=1);

namespace App\Services\Notifications\Configs;

final readonly class GenericWebhookConfig implements NotificationConfig
{
    public function __construct(
        public string $url,
        public ?string $secret = null,
        public bool $verifySsl = true,
        public string $contentType = 'json',
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            url: isset($data['url']) && is_string($data['url']) ? $data['url'] : '',
            secret: isset($data['secret']) && is_string($data['secret']) ? $data['secret'] : null,
            verifySsl: ! isset($data['verify_ssl']) || $data['verify_ssl'],
            contentType: isset($data['content_type']) && is_string($data['content_type']) ? $data['content_type'] : 'json',
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'secret' => $this->secret,
            'verify_ssl' => $this->verifySsl,
            'content_type' => $this->contentType,
        ];
    }
}
