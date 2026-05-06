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

final class SubscriptionsByAccountChart extends ChartWidget
{
    use HasSubscriptionFilters;
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    public function getHeading(): string
    {
        return Str::ucfirst(__('account.plural_label'));
    }

    protected function getData(): array
    {
        $query = Subscription::query();
        $this->applyFilters($query);

        $subscriptions = $query->with('account')->get();

        return resolve(SubscriptionService::class)->getChartAllocation($subscriptions, 'account');
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getTablePage(): string
    {
        return ListSubscriptions::class;
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
