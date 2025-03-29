<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Portfolio;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class PortfolioChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = null;

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
        'scales' => [
            'y' => [
                'display' => false,
            ],
            'x' => [
                'display' => false,
            ],
        ],
    ];

    public function getHeading(): Htmlable|string|null
    {
        return __('portfolio.navigation_label');
    }

    protected function getData(): array
    {
        $portfolios = Portfolio::whereActive(true)->get();
        $portfoliosLabels = [];
        $portfoliosData = [];
        $backgroundColors = [];
        foreach ($portfolios as $portfolio) {
            $portfoliosLabels[] = $portfolio->name;
            $portfoliosData[] = $portfolio->market_value;
            $backgroundColors[] = $portfolio->color;
        }

        return [
            'datasets' => [
                [
                    'data' => $portfoliosData,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $portfoliosLabels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
