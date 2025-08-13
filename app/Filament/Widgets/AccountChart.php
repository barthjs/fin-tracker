<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class AccountChart extends ChartWidget
{
    protected static ?int $sort = 2;

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

    public function getHeading(): Htmlable|string|null
    {
        return __('account.navigation_label');
    }

    protected function getData(): array
    {
        $accounts = Account::whereActive(true)
            ->orderBy('balance', 'desc')
            ->get();

        $labels = [];
        $series = [];
        $colors = [];

        foreach ($accounts as $account) {
            $labels[] = $account->name;
            $series[] = $account->balance;
            $colors[] = $account->color;
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
