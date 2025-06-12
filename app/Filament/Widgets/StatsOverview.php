<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\CategoryStatistic;
use App\Models\Portfolio;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Number;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $currency = Account::getCurrency();
        $year = Carbon::now()->year;
        $monthColumn = mb_strtolower(Carbon::now()->format('M'));
        $months = array_keys(__('category_statistic.columns'));

        $totalAssets = Number::currency(
            Account::getActiveSum() + Portfolio::getActiveSum(),
            $currency
        );

        $expenseSum = $this->getCategorySumByMonth(TransactionType::expense, $year, $monthColumn);
        $revenueSum = $this->getCategorySumByMonth(TransactionType::revenue, $year, $monthColumn);

        $expenseChart = $this->getCategoryChartData(TransactionType::expense, $year, $months);
        $revenueChart = $this->getCategoryChartData(TransactionType::revenue, $year, $months);

        return [
            Stat::make(__('widget.stats.total_assets'), $totalAssets)
                ->color('info'),

            Stat::make(__('widget.stats.expenses_this_month'), $expenseSum)
                ->color('danger')
                ->chart($expenseChart),

            Stat::make(__('widget.stats.revenues_this_month'), $revenueSum)
                ->color('success')
                ->chart($revenueChart),
        ];
    }

    private function getCategorySumByMonth(TransactionType $type, int $year, string $month): string
    {
        $sum = CategoryStatistic::where('year', $year)
            ->whereHas('category', fn ($query) => $query->where('type', $type))
            ->sum($month) / 100;

        return Number::currency($sum, Account::getCurrency());
    }

    private function getCategoryChartData(TransactionType $type, int $year, array $months): array
    {
        return array_map(fn ($month) => CategoryStatistic::where('year', $year)
            ->whereHas('category', fn ($query) => $query->where('type', $type))
            ->sum($month) / 100,
            $months
        );
    }
}
