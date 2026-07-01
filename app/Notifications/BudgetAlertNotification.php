<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Contracts\HasDynamicNotificationTarget;
use App\Data\BudgetUsage;
use App\Data\NotificationPayload;
use App\Enums\BudgetAlert;
use App\Enums\Currency;
use App\Models\Budget;
use App\Models\NotificationTarget;
use App\Notifications\Channels\DynamicTargetChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

/**
 * Informs the budget owner that the spending reached the warning threshold or exceeded the budget.
 */
final class BudgetAlertNotification extends Notification implements HasDynamicNotificationTarget, ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Budget $budget,
        private readonly NotificationTarget $target,
        private readonly BudgetUsage $usage,
        private readonly BudgetAlert $alert,
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
     * Builds title, body and metadata of the budget alert in the locale of the user.
     */
    public function toNotificationPayload(): NotificationPayload
    {
        Number::useLocale($this->budget->user->locale);

        $currency = Currency::getCurrency();

        $category = $this->budget->category->name;
        $spent = Number::currency($this->usage->spent, $currency);
        $budgeted = Number::currency($this->usage->budgeted, $currency);
        $percentage = (int) round($this->usage->percentage);

        return new NotificationPayload(
            title: __('budget.notifications.'.$this->alert->value.'_title', ['category' => $category]),
            body: __('budget.notifications.'.$this->alert->value.'_body', [
                'category' => $category,
                'percentage' => $percentage,
                'spent' => $spent,
                'budgeted' => $budgeted,
            ]),
            metadata: [
                'budget_id' => $this->budget->id,
                'category_id' => $this->budget->category_id,
                'alert' => $this->alert->value,
                'percentage' => $percentage,
            ]
        );
    }
}
