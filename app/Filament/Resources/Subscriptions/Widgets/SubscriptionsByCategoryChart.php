<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions\Widgets;

use App\Filament\Concerns\HasSubscriptionFilters;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Illuminate\Support\Str;

final class SubscriptionsByCategoryChart extends ChartWidget
{
    use HasSubscriptionFilters, InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    public function getHeading(): string
    {
        return Str::ucfirst(__('category.plural_label'));
    }

    protected function getData(): array
    {
        $query = Subscription::query();
        $this->applyFilters($query);

        $subscriptions = $query->with('category')->get();

        return app(SubscriptionService::class)->getChartAllocation($subscriptions, 'category');
    }

    protected function getTablePage(): string
    {
        return ListSubscriptions::class;
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
