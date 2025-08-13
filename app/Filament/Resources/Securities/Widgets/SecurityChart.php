<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities\Widgets;

use App\Models\Security;
use Filament\Widgets\ChartWidget;

class SecurityChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

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

    protected function getData(): array
    {
        $securities = Security::whereActive(true)
            ->orderBy('market_value', 'desc')
            ->get();

        $labels = [];
        $series = [];
        $colors = [];

        foreach ($securities as $security) {
            $labels[] = $security->name;
            $series[] = (float) $security->market_value;
            $colors[] = $security->color;
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
