<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Portfolio;
use Illuminate\Contracts\Support\Htmlable;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PortfolioChart extends ApexChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $chartId = 'portfolioChart';

    protected static bool $deferLoading = true;

    protected static ?string $pollingInterval = null;

    public function getHeading(): Htmlable|string|null
    {
        return __('portfolio.navigation_label');
    }

    protected function getOptions(): array
    {
        $portfolios = Portfolio::whereActive(true)->get();

        $labels = [];
        $series = [];
        $colors = [];

        foreach ($portfolios as $portfolio) {
            $labels[] = $portfolio->name;
            $series[] = (float) $portfolio->market_value;
            $colors[] = $portfolio->color;
        }

        return [
            'chart' => [
                'type' => 'pie',
            ],
            'labels' => $labels,
            'series' => $series,
            'colors' => $colors,
            'legend' => [
                'show' => false,
            ],
        ];
    }
}
