<?php

declare(strict_types=1);

namespace App\Services\Notifications\Strategies;

use App\Data\NotificationPayload;
use App\Mail\NotificationMail;
use App\Models\NotificationTarget;
use App\Services\Notifications\Configs\EmailConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

/**
 * Send notifications via email.
 */
final readonly class EmailStrategy implements NotificationSenderStrategy
{
    public function send(NotificationTarget $target, NotificationPayload $payload): void
    {
        $config = $target->getConfig();

        if (! $config instanceof EmailConfig || $config->email === '') {
            return;
        }

        try {
            Mail::to($config->email)->send(
                new NotificationMail(
                    title: $payload->title,
                    body: $payload->body,
                )
            );
        } catch (Throwable $e) {
            Log::error('Email notification failed', [
                'email' => $config->email,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Email notification failed: '.$e->getMessage());
        }
    }
}
