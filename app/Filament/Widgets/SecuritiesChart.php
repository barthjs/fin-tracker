<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Security;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class SecuritiesChart extends ChartWidget
{
    protected static ?int $sort = 4;

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
        return __('security.navigation_label');
    }

    protected function getData(): array
    {
        $securities = Security::whereActive(true)->get();
        $securitiesLabels = [];
        $securitiesData = [];
        $backgroundColors = [];
        foreach ($securities as $security) {
            $securitiesLabels[] = $security->name;
            $securitiesData[] = $security->total_quantity;
            $backgroundColors[] = $security->color;
        }

        return [
            'datasets' => [
                [
                    'data' => $securitiesData,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $securitiesLabels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
