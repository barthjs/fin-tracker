<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Contracts\HasDynamicNotificationTarget;
use App\Data\NotificationPayload;
use App\Models\NotificationTarget;
use App\Models\Subscription;
use App\Notifications\Channels\DynamicTargetChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

final class SubscriptionReminderNotification extends Notification implements HasDynamicNotificationTarget, ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Subscription $subscription,
        private readonly NotificationTarget $target
    ) {
        Number::useLocale($this->subscription->user->locale);
    }

    /**
     * @return array<class-string>
     */
    public function via(): array
    {
        return [DynamicTargetChannel::class];
    }

    public function getNotificationTarget(): NotificationTarget
    {
        return $this->target;
    }

    public function toNotificationPayload(): NotificationPayload
    {
        $amount = Number::currency(
            $this->subscription->amount,
            $this->subscription->account->currency->value
        );

        return new NotificationPayload(
            title: __('subscription.notifications.reminder_title', ['name' => $this->subscription->name]),
            body: __('subscription.notifications.reminder_body', [
                'name' => $this->subscription->name,
                'amount' => $amount,
                'date' => $this->subscription->next_payment_date->toDateString(),
            ]),
            metadata: [
                'subscription_id' => $this->subscription->id,
                'due_date' => $this->subscription->next_payment_date->toIso8601String(),
            ]
        );
    }
}
