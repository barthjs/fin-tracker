<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class AccountChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

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
        return __('account.navigation_label');
    }

    protected function getData(): array
    {
        $accounts = Account::whereActive(true)->get();
        $accountsLabels = [];
        $accountsData = [];
        $backgroundColors = [];
        foreach ($accounts as $account) {
            $accountsLabels[] = $account->name;
            $accountsData[] = $account->balance;
            $backgroundColors[] = $account->color;
        }

        return [
            'datasets' => [
                [
                    'data' => $accountsData,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $accountsLabels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
