<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\BudgetUsage;
use App\Enums\PeriodUnit;
use App\Enums\TransactionType;
use App\Models\Budget;
use App\Models\Transaction;
use Carbon\CarbonImmutable;

/**
 * Calculate the spending of a budget's category within the budget's current period.
 *
 * Periods are counted from the budget's start date in the user's timezone.
 * An ended budget reports its final period, which is cut off at the end date.
 */
final class CalculateBudgetUsage
{
    /**
     * Returns budgeted amount, spending, remainder and percentage for the current period.
     */
    public function __invoke(Budget $budget): BudgetUsage
    {
        $budget->loadMissing('user');

        $timezone = $budget->user->timezone;

        [$periodStart, $periodEnd] = $this->currentPeriodWindow($budget, $timezone);

        $spent = (float) Transaction::query()
            ->where('category_id', $budget->category_id)
            ->where('type', TransactionType::Expense)
            ->where('date_time', '>=', $periodStart->setTimezone('UTC'))
            ->where('date_time', '<', $periodEnd->setTimezone('UTC'))
            ->sum('amount');

        $budgeted = $budget->amount;
        $remaining = $budgeted - $spent;
        $percentage = $budgeted > 0.0 ? ($spent / $budgeted) * 100.0 : 0.0;

        return new BudgetUsage(
            periodStart: $periodStart,
            periodEnd: $periodEnd,
            budgeted: $budgeted,
            spent: $spent,
            remaining: $remaining,
            percentage: $percentage,
        );
    }

    /**
     * Determines the period window [start, end) that contains today, or the final window of an ended budget.
     *
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function currentPeriodWindow(Budget $budget, string $timezone): array
    {
        $anchor = $budget->starts_at->shiftTimezone($timezone)->startOfDay();
        $reference = CarbonImmutable::now($timezone);

        $endsAt = null;
        if ($budget->ends_at !== null) {
            // The end date is inclusive, so the window closes at the start of the following day
            $endsAt = $budget->ends_at->shiftTimezone($timezone)->startOfDay()->addDay();

            if ($endsAt->lessThanOrEqualTo($reference)) {
                $reference = $endsAt->subDay();
            }
        }

        $elapsedPeriods = $this->elapsedPeriods($anchor, $reference, $budget);

        $periodStart = $this->shift($anchor, $budget, $elapsedPeriods);
        $periodEnd = $this->shift($anchor, $budget, $elapsedPeriods + 1);

        if ($endsAt !== null && $endsAt->lessThan($periodEnd)) {
            $periodEnd = $endsAt;
        }

        return [$periodStart, $periodEnd];
    }

    /**
     * Counts the complete periods between the anchor and the reference date.
     */
    private function elapsedPeriods(CarbonImmutable $anchor, CarbonImmutable $reference, Budget $budget): int
    {
        if ($reference->lessThan($anchor)) {
            return 0;
        }

        $difference = match ($budget->period_unit) {
            PeriodUnit::Day => $anchor->diffInDays($reference),
            PeriodUnit::Week => $anchor->diffInWeeks($reference),
            PeriodUnit::Month => $anchor->diffInMonths($reference),
            PeriodUnit::Year => $anchor->diffInYears($reference),
        };

        $elapsedPeriods = max(0, (int) floor($difference / $this->frequency($budget)));

        // The fractional difference is only an estimate across month lengths and DST changes
        while ($elapsedPeriods > 0 && $this->shift($anchor, $budget, $elapsedPeriods)->greaterThan($reference)) {
            // Carbon's fractional differences have not been observed to overshoot, the guard keeps the window correct
            // @codeCoverageIgnoreStart
            $elapsedPeriods--;
            // @codeCoverageIgnoreEnd
        }

        while ($this->shift($anchor, $budget, $elapsedPeriods + 1)->lessThanOrEqualTo($reference)) {
            $elapsedPeriods++;
        }

        return $elapsedPeriods;
    }

    /**
     * Moves the anchor forward by the given number of periods.
     *
     * Shifting from the anchor instead of the previous period keeps the original day of month,
     * so a budget starting on the 31st does not drift to the 28th after February.
     */
    private function shift(CarbonImmutable $anchor, Budget $budget, int $periods): CarbonImmutable
    {
        $units = $periods * $this->frequency($budget);

        return match ($budget->period_unit) {
            PeriodUnit::Day => $anchor->addDays($units),
            PeriodUnit::Week => $anchor->addWeeks($units),
            PeriodUnit::Month => $anchor->addMonthsNoOverflow($units),
            PeriodUnit::Year => $anchor->addYearsNoOverflow($units),
        };
    }

    /**
     * Returns the period frequency, at least one.
     */
    private function frequency(Budget $budget): int
    {
        return max(1, $budget->period_frequency);
    }
}
