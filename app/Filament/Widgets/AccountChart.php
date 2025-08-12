<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Account;
use Illuminate\Contracts\Support\Htmlable;

class AccountChart
{
    protected static ?int $sort = 2;

    protected static ?string $chartId = 'accountChart';

    protected static bool $deferLoading = true;

    protected static ?string $pollingInterval = null;

    public function getHeading(): Htmlable|string|null
    {
        return __('account.navigation_label');
    }

    protected function getOptions(): array
    {
        $accounts = Account::whereActive(true)->get();

        $labels = [];
        $series = [];
        $colors = [];

        foreach ($accounts as $account) {
            $labels[] = $account->name;
            $series[] = $account->balance;
            $colors[] = $account->color;
        }

        return [
            'chart' => [
                'type' => 'pie',
            ],
            'series' => $series,
            'labels' => $labels,
            'colors' => $colors,
            'legend' => [
                'show' => false,
            ],
        ];
    }
}
