<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\CategoryStatistic;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final class StatisticService
{
    /**
     * Updates the category statistics for a specific month based on the user's timezone.
     */
    public function updateCategoryStatistics(string $categoryId, CarbonInterface $date): void
    {
        if (auth()->guest()) {
            $category = Category::query()->with('user')->find($categoryId);
            if ($category === null) {
                return;
            }
            $timezone = $category->user->timezone;
        } else {
            $timezone = auth()->user()->timezone;
        }

        $dateInUserTime = $date->setTimezone($timezone);

        $year = (int) $dateInUserTime->format('Y');
        $monthColumn = mb_strtolower($dateInUserTime->format('M'));

        // Calculate the full month range in User Time, then convert to UTC for the query
        /** @var CarbonImmutable $startOfPeriod */
        $startOfPeriod = CarbonImmutable::create($year, (int) $dateInUserTime->format('m'), 1, 0, 0, 0, $timezone);
        $endOfPeriod = $startOfPeriod->endOfMonth();

        $sumPerMonth = Transaction::query()
            ->where('category_id', $categoryId)
            ->where('type', '!=', TransactionType::Transfer)
            ->whereBetween('date_time', [
                $startOfPeriod->setTimezone('UTC'),
                $endOfPeriod->setTimezone('UTC'),
            ])
            ->sum('amount');

        $stat = CategoryStatistic::updateOrCreate(
            ['category_id' => $categoryId, 'year' => $year],
            [$monthColumn => $sumPerMonth]
        );

        if ($stat->yearlySum() === 0.0) {
            $stat->delete();
        }
    }
}
