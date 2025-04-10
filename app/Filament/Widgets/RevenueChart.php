<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Contracts\Support\Htmlable;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueChart extends ApexChartWidget
{
    protected static ?int $sort = 5;

    protected static ?string $chartId = 'revenueChart';

    protected static bool $deferLoading = true;

    protected static ?string $pollingInterval = null;

    public function getHeading(): Htmlable|string|null
    {
        return __('table.filter.revenues');
    }

    protected function getOptions(): array
    {
        $data = Category::getChartData(TransactionType::revenue);

        return [
            'chart' => [
                'type' => 'pie',
            ],
            'series' => $data['series'],
            'labels' => $data['labels'],
            'colors' => $data['colors'],
            'legend' => [
                'show' => false,
            ],
        ];
    }
}
