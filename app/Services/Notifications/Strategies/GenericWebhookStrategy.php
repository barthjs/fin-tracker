<?php

declare(strict_types=1);

namespace App\Services\Notifications\Strategies;

use App\Data\NotificationPayload;
use App\Models\NotificationTarget;
use App\Services\Notifications\Configs\GenericWebhookConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Sends notifications to a generic webhook.
 */
final readonly class GenericWebhookStrategy implements NotificationSenderStrategy
{
    public function send(NotificationTarget $target, NotificationPayload $payload): void
    {
        $config = $target->getConfig();

        if (! $config instanceof GenericWebhookConfig || $config->url === '') {
            return;
        }

        $data = $payload->toArray();

        if ($config->contentType === 'form') {
            $bodyContent = http_build_query($data, '', '&', PHP_QUERY_RFC1738);
            $mimeType = 'application/x-www-form-urlencoded';
        } else {
            $bodyContent = json_encode($data, JSON_THROW_ON_ERROR);
            $mimeType = 'application/json';
        }

        $client = Http::withUserAgent(config()->string('app.name').'/'.config()->string('app.version'))
            ->timeout(10);

        if (! $config->verifySsl) {
            $client->withoutVerifying();
        }

        $secret = $config->secret ?: config()->string('app.webhook_secret');

        if (! empty($secret)) {
            $signature = hash_hmac(algo: 'sha256', data: $bodyContent, key: $secret);
            $client->withHeaders([
                'X-Signature-256' => "sha256=$signature",
            ]);
        } else {
            Log::warning('Sending webhook to '.$config->url.' without secret.');
        }

        $response = $client
            ->withBody($bodyContent, $mimeType)
            ->post($config->url);

        if ($response->failed()) {
            $errorBody = $response->body();
            $truncatedBody = mb_substr($errorBody, 0, 200);

            throw new RuntimeException(sprintf(
                'Generic webhook failed with status: %d. Response: %s%s',
                $response->status(),
                $truncatedBody,
                mb_strlen($errorBody) > 200 ? '...' : ''
            ));
        }
    }
}
