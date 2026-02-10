<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions\Widgets;

use App\Enums\Currency;
use App\Filament\Concerns\HasSubscriptionFilters;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class SubscriptionStats extends StatsOverviewWidget
{
    use HasSubscriptionFilters, InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $query = Subscription::query();
        $this->applyFilters($query);

        $subscriptions = $query->get();
        $subCount = $subscriptions->count();
        $currency = Currency::getCurrency();

        $stats = app(SubscriptionService::class)->calculateStats($subscriptions);

        $avgMonthlyPerSub = $subCount > 0 ? $stats['monthly_avg'] / $subCount : 0;

        $dueColor = match (true) {
            $stats['due_this_month'] === 0.0 => 'success',
            $stats['due_this_month'] > $stats['monthly_avg'] => 'warning',
            default => 'info',
        };

        return [
            Stat::make(__('subscription.stats.monthly_cost'), Number::currency($stats['monthly_avg'], $currency))
                ->description(__('subscription.stats.avg_per_sub', [
                    'amount' => Number::currency($avgMonthlyPerSub, $currency),
                ]))
                ->color('primary'),

            Stat::make(__('subscription.stats.yearly_cost'), Number::currency($stats['yearly_avg'], $currency))
                ->color('gray'),

            Stat::make(__('subscription.stats.amount_due_this_month'), Number::currency($stats['due_this_month'], $currency))
                ->color($dueColor)
                ->chart($stats['daily_chart']),
        ];
    }

    protected function getTablePage(): string
    {
        return ListSubscriptions::class;
    }
}
