<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\RecalculatePortfolioMarketValue;
use App\Enums\SecurityRegion;
use App\Enums\SecuritySector;
use App\Models\Security;
use App\Models\Trade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final readonly class SecurityService
{
    private const string UNASSIGNED_CHART_COLOR = '#9ca3af';

    public function __construct(
        private RecalculatePortfolioMarketValue $recalculatePortfolioMarketValue,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Security $security, array $data): Security
    {
        return DB::transaction(function () use ($security, $data): Security {
            $oldPrice = $security->price;

            $security->update($data);

            if ($oldPrice !== $security->price) {
                /** @var array<string> $portfolios */
                $portfolios = Trade::query()->where('security_id', $security->id)
                    ->distinct(['portfolio_id'])
                    ->pluck('portfolio_id')
                    ->toArray();

                if (! empty($portfolios)) {
                    foreach ($portfolios as $portfolio) {
                        ($this->recalculatePortfolioMarketValue)($portfolio);
                    }
                }
            }

            return $security;
        });
    }

    /**
     * Sums the market value of the given securities per classification case as chart data.
     *
     * @param  Builder<Security>  $query
     * @param  class-string<SecurityRegion>|class-string<SecuritySector>  $enum
     * @return array{labels: list<string>, datasets: list<array{data: list<float>, backgroundColor: list<string>, hoverOffset: int}>}
     */
    public function getChartAllocation(Builder $query, string $column, string $enum): array
    {
        /** @var list<object{value: string|null, total: string}> $rows */
        $rows = $query->toBase()
            ->select($column.' as value')
            ->selectRaw('SUM(market_value) as total')
            ->groupBy($column)
            ->orderByDesc('total')
            ->get()
            ->all();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($rows as $row) {
            $case = $row->value === null ? null : $enum::from($row->value);

            $labels[] = $case?->getLabel() ?? __('security.unassigned');
            $data[] = (float) $row->total;
            $colors[] = $case?->getChartColor() ?? self::UNASSIGNED_CHART_COLOR;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'hoverOffset' => 4,
                ],
            ],
        ];
    }
}
