<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Category;
use Filament\Widgets\ChartWidget;

final class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 5;

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
        return __('table.filter.revenues');
    }

    protected function getData(): array
    {
        $data = Category::getChartData(TransactionType::Revenue);

        return [
            'datasets' => [
                [
                    'data' => $data['series'],
                    'backgroundColor' => $data['colors'],
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
