<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\CheckBudgetThreshold;
use App\Enums\NotificationEventType;
use App\Models\Budget;
use Illuminate\Support\Facades\DB;

final readonly class BudgetService
{
    /**
     * Fields that define what a budget measures, so a change starts its alerts over.
     */
    private const array ALERT_RESETTING_FIELDS = [
        'category_id',
        'amount',
        'warning_threshold',
        'period_unit',
        'period_frequency',
        'starts_at',
    ];

    public function __construct(
        private CheckBudgetThreshold $checkBudgetThreshold,
    ) {}

    /**
     * Create a budget, sync its notification assignments and run a threshold check.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Budget
    {
        return DB::transaction(function () use ($data): Budget {
            $budget = new Budget();

            $hasTargets = array_key_exists('exceed_targets', $data);
            $exceedTargets = $budget->extractNotificationTargets($data, 'exceed_targets');

            $budget->fill($data);
            $budget->save();

            if ($hasTargets) {
                $budget->syncNotificationAssignments(NotificationEventType::BUDGET_ALERT, $exceedTargets);
            }

            ($this->checkBudgetThreshold)($budget->category_id);

            return $budget;
        });
    }

    /**
     * Update a budget, sync its notification assignments and run a threshold check.
     *
     * Notification targets are only synced when the data contains them, so partial updates keep the existing ones.
     * Changing the limit, the category or the period allows the alerts of the current period to be sent again.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Budget $budget, array $data): Budget
    {
        return DB::transaction(function () use ($budget, $data): Budget {
            $hasTargets = array_key_exists('exceed_targets', $data);
            $exceedTargets = $budget->extractNotificationTargets($data, 'exceed_targets');

            $budget->update($data);

            // A changed limit, category or period is a new budget for the running period, so its alerts may be sent again
            if ($budget->wasChanged(self::ALERT_RESETTING_FIELDS)) {
                $budget->updateQuietly([
                    'last_warned_at' => null,
                    'last_exceeded_at' => null,
                ]);
            }

            if ($hasTargets) {
                $budget->syncNotificationAssignments(NotificationEventType::BUDGET_ALERT, $exceedTargets);
            }

            ($this->checkBudgetThreshold)($budget->category_id);

            return $budget;
        });
    }
}
