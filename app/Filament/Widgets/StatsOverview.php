<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\CategoryStatistic;
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
        $totalAssets = Account::sum('balance');
        $totalAssets = Number::currency($totalAssets, Account::getCurrency());

        $monthColumn = strtolower(Carbon::createFromDate(null, Carbon::today()->month)->format('M'));
        $year = Carbon::now()->year;

        $expenseSum = CategoryStatistic::where('year', '=', $year)->whereHas('category', function ($query) {
            $query->where('type', TransactionType::expense);
        })->sum($monthColumn);
        $expenseSum = Number::currency($expenseSum, Account::getCurrency());

        $revenueSum = CategoryStatistic::where('year', '=', $year)->whereHas('category', function ($query) {
            $query->where('type', TransactionType::revenue);
        })->sum($monthColumn);
        $revenueSum = Number::currency($revenueSum, Account::getCurrency());

        return [
            Stat::make(__('widget.stats.total_assets'), $totalAssets)
                ->color('warning'),
            Stat::make(__('widget.stats.expenses_this_month'), $expenseSum)
                ->color('danger'),
            Stat::make(__('widget.stats.revenues_this_month'), $revenueSum)
                ->color('success'),
        ];
    }
}