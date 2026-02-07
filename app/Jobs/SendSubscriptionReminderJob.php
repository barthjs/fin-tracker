<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\NotificationEventType;
use App\Models\Subscription;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class SendSubscriptionReminderJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Subscription $subscription
    ) {}

    public function uniqueId(): string
    {
        return $this->subscription->id;
    }

    public function handle(): void
    {
        if (! $this->subscription->is_active || ! $this->subscription->remind_before_payment) {
            return;
        }

        $targets = $this->subscription->getNotificationTargetsForEvent(
            NotificationEventType::SUBSCRIPTION_REMINDER
        );

        if ($targets->isEmpty()) {
            $this->markAsReminded();

            return;
        }

        $user = $this->subscription->user;

        foreach ($targets as $target) {
            try {
                $user->notify(new SubscriptionReminderNotification($this->subscription, $target));
            } catch (Throwable $e) {
                report($e);
            }
        }

        $this->markAsReminded();
    }

    private function markAsReminded(): void
    {
        $this->subscription->updateQuietly([
            'last_reminded_at' => now(),
        ]);
    }
}
