<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Category;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

final class ExpenseChart extends ChartWidget
{
    protected static ?int $sort = 4;

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
        return Str::ucfirst(__('table.filter.expenses'));
    }

    protected function getData(): array
    {
        $data = Category::getChartData(TransactionType::Expense);

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
