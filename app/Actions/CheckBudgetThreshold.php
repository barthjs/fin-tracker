<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\BudgetUsage;
use App\Enums\BudgetAlert;
use App\Enums\NotificationEventType;
use App\Models\Budget;
use App\Notifications\BudgetAlertNotification;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Check the budget of a category within the saving transaction and queue its alert notifications.
 *
 * Each period gets at most one warning and one exceeded alert. The claim of the alert and the queued notifications
 * are part of the same database transaction as the booking, so they are committed or rolled back together.
 * The conditional update of the claim locks the budget row, so concurrent saves cannot send the alert twice.
 */
final readonly class CheckBudgetThreshold
{
    public function __construct(
        private CalculateBudgetUsage $calculateBudgetUsage,
    ) {}

    /**
     * Runs the check for the active, notifying budget of the category against the data of the current transaction.
     */
    public function __invoke(string $categoryId): void
    {
        DB::transaction(function () use ($categoryId): void {
            $budget = Budget::query()
                ->withoutGlobalScopes()
                ->where('category_id', $categoryId)
                ->where('is_active', true)
                ->where('notify_on_exceed', true)
                // Serializes concurrent checks of the budget, so each usage sum includes the other bookings
                ->lockForUpdate()
                ->first();

            if ($budget === null) {
                return;
            }

            $this->check($budget);
        });
    }

    /**
     * Sends the alert that matches the current usage, unless it was already sent in this period.
     */
    private function check(Budget $budget): void
    {
        $usage = ($this->calculateBudgetUsage)($budget);

        $alert = $this->dueAlert($budget, $usage);

        if (! $alert instanceof BudgetAlert) {
            return;
        }

        if (! $this->claim($budget, $alert, $usage->periodStart)) {
            return;
        }

        $targets = $budget->getNotificationTargetsForEvent(NotificationEventType::BUDGET_ALERT);

        foreach ($targets as $target) {
            $budget->user->notify(new BudgetAlertNotification($budget, $target, $usage, $alert));
        }
    }

    /**
     * Returns the alert that has to be sent for the usage, or null when nothing is due.
     */
    private function dueAlert(Budget $budget, BudgetUsage $usage): ?BudgetAlert
    {
        $exceededThisPeriod = $this->sentThisPeriod($budget->last_exceeded_at, $usage->periodStart);

        if ($usage->hasReachedThreshold(100)) {
            return $exceededThisPeriod ? null : BudgetAlert::Exceeded;
        }

        // A warning after an exceeded alert in the same period would only report a lower stage again
        if ($exceededThisPeriod || $budget->warning_threshold === null) {
            return null;
        }

        if (! $usage->hasReachedThreshold($budget->warning_threshold)) {
            return null;
        }

        if ($this->sentThisPeriod($budget->last_warned_at, $usage->periodStart)) {
            return null;
        }

        return BudgetAlert::Warning;
    }

    /**
     * Whether an alert timestamp lies within the period that starts at the given date.
     */
    private function sentThisPeriod(?CarbonImmutable $sentAt, CarbonImmutable $periodStart): bool
    {
        if (! $sentAt instanceof CarbonImmutable) {
            return false;
        }

        return $sentAt->greaterThanOrEqualTo($periodStart);
    }

    /**
     * Stores the send time of the alert if it was not sent in this period yet, and reports whether this call won.
     */
    private function claim(Budget $budget, BudgetAlert $alert, CarbonImmutable $periodStart): bool
    {
        $column = match ($alert) {
            BudgetAlert::Warning => 'last_warned_at',
            BudgetAlert::Exceeded => 'last_exceeded_at',
        };

        $claimed = Budget::query()
            ->withoutGlobalScopes()
            ->whereKey($budget->id)
            ->where(function (Builder $query) use ($column, $periodStart): void {
                $query->whereNull($column)->orWhere($column, '<', $periodStart->setTimezone('UTC'));
            })
            ->update([$column => now()]);

        return $claimed === 1;
    }
}
