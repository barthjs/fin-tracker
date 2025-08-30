<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Portfolio;
use Filament\Widgets\ChartWidget;

final class PortfolioChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected ?array $options = [
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

    public function getHeading(): string
    {
        return __('portfolio.plural_label');
    }

    protected function getData(): array
    {
        $portfolios = Portfolio::where('is_active', true)
            ->orderBy('market_value', 'desc')
            ->get();

        $labels = [];
        $series = [];
        $colors = [];

        foreach ($portfolios as $portfolio) {
            $labels[] = $portfolio->name;
            $series[] = (float) $portfolio->market_value;
            $colors[] = $portfolio->color;
        }

        return [
            'datasets' => [
                [
                    'data' => $series,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
