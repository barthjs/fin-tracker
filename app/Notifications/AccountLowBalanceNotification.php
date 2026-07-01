<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Contracts\HasDynamicNotificationTarget;
use App\Data\NotificationPayload;
use App\Models\Account;
use App\Models\NotificationTarget;
use App\Notifications\Channels\DynamicTargetChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

final class AccountLowBalanceNotification extends Notification implements HasDynamicNotificationTarget, ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Account $account,
        private readonly NotificationTarget $target,
    ) {}

    /**
     * Sends the notification through the channel of its notification target.
     *
     * @return array<class-string>
     */
    public function via(): array
    {
        return [DynamicTargetChannel::class];
    }

    /**
     * Returns the target this notification is sent to.
     */
    public function getNotificationTarget(): NotificationTarget
    {
        return $this->target;
    }

    /**
     * Builds title, body and metadata of the low-balance alert in the locale of the user.
     */
    public function toNotificationPayload(): NotificationPayload
    {
        Number::useLocale($this->account->user->locale);

        $currency = $this->account->currency->value;

        $balance = Number::currency($this->account->balance, $currency);
        $threshold = Number::currency($this->account->low_balance_threshold, $currency);

        return new NotificationPayload(
            title: __('account.notifications.low_balance_title', ['name' => $this->account->name]),
            body: __('account.notifications.low_balance_body', [
                'name' => $this->account->name,
                'balance' => $balance,
                'threshold' => $threshold,
            ]),
            metadata: [
                'account_id' => $this->account->id,
                'balance' => $this->account->balance,
            ]
        );
    }
}
