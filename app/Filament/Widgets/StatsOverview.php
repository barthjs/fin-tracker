<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\CategoryStatistic;
use App\Models\Portfolio;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Number;

final class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $year = Carbon::now()->year;
        $monthColumn = mb_strtolower(Carbon::now()->format('M'));

        $totalAssets = Number::currency(
            Account::getActiveBalanceSum() + Portfolio::getActiveMarketValueSum(),
            Currency::getCurrency()
        );

        $expenseSum = $this->getCategorySumByMonth(TransactionType::Expense, $year, $monthColumn);
        $revenueSum = $this->getCategorySumByMonth(TransactionType::Revenue, $year, $monthColumn);

        $expenseChart = $this->getCategoryChartData(TransactionType::Expense, $year);
        $revenueChart = $this->getCategoryChartData(TransactionType::Revenue, $year);

        return [
            Stat::make(__('Total Assets'), $totalAssets)
                ->color('info'),

            Stat::make(__('Expenses this month'), $expenseSum)
                ->color('danger')
                ->chart($expenseChart),

            Stat::make(__('Revenues this month'), $revenueSum)
                ->color('success')
                ->chart($revenueChart),
        ];
    }

    private function getCategorySumByMonth(TransactionType $type, int $year, string $month): string
    {
        $sum = (float) CategoryStatistic::where('year', $year)
            ->whereHas('category', fn (Builder $query): Builder => $query->where('type', $type))
            ->sum($month);

        return (string) Number::currency($sum, Currency::getCurrency());
    }

    /**
     * @return array<int, float>
     */
    private function getCategoryChartData(TransactionType $type, int $year): array
    {
        $row = CategoryStatistic::query()
            ->selectRaw(collect(CategoryStatistic::MONTHS)
                ->map(fn (string $m) => "SUM($m) as $m")
                ->implode(', '))
            ->where('year', $year)
            ->whereHas('category', fn (Builder $query): Builder => $query->where('type', $type))
            ->first();

        if ($row === null) {
            return array_fill(0, count(CategoryStatistic::MONTHS), 0.0);
        }

        /** @var array<string, float|int|null> $values */
        $values = $row->toArray();

        return array_map(
            fn (string $m): float => (float) ($values[$m] ?? 0),
            CategoryStatistic::MONTHS
        );
    }
}
