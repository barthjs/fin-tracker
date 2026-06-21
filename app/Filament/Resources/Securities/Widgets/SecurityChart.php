<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities\Widgets;

use App\Filament\Concerns\HasSecurityFilters;
use App\Filament\Resources\Securities\Pages\ListSecurities;
use App\Models\Security;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Illuminate\Support\Collection;

final class SecurityChart extends ChartWidget
{
    use HasSecurityFilters;
    use InteractsWithPageTable;

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
        $query = Security::query()->orderBy('market_value', 'desc');
        $securities = $this->applyFilters($query)->get();

        $labels = [];
        $series = [];
        $colors = [];

        /** @var Collection<int, Security> $securities */
        foreach ($securities as $security) {
            $labels[] = $security->name;
            $series[] = $security->market_value;
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

    /**
     * @codeCoverageIgnore
     */
    protected function getTablePage(): string
    {
        return ListSecurities::class;
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
