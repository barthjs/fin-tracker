<?php

declare(strict_types=1);

namespace App\Filament\Resources\SecurityResource\Widgets;

use App\Models\Security;

class SecurityChart
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $chartId = 'securityChart';

    protected static bool $deferLoading = true;

    protected static ?string $pollingInterval = null;

    protected function getOptions(): array
    {
        $securities = Security::whereActive(true)->get();

        $labels = [];
        $series = [];
        $colors = [];

        foreach ($securities as $security) {
            $labels[] = $security->name;
            $series[] = (float) $security->market_value;
            $colors[] = $security->color;
        }

        return [
            'chart' => [
                'type' => 'pie',
            ],
            'labels' => $labels,
            'series' => $series,
            'colors' => $colors,
        ];
    }
}
