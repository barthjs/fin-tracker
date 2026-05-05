<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\CategoryStatistic;
use Illuminate\Support\Facades\Date;

/**
 * Build the chart data (labels, series, colors) for all active categories
 * of a given transaction type, based on the current month's statistics.
 */
final class GetCategoryChartData
{
    /**
     * @return array{labels: list<string>, series: list<float>, colors: list<string|null>}
     */
    public function __invoke(TransactionType $type): array
    {
        $categories = Category::query()->where('is_active', true)->where('type', $type)->get();

        $monthColumn = mb_strtolower(Date::createFromDate(null, Date::today()->month)->format('M'));
        $year = Date::now()->year;

        $data = [];

        foreach ($categories as $category) {
            /** @var float $sum */
            $sum = CategoryStatistic::query()->where('category_id', $category->id)
                ->where('year', $year)
                ->value($monthColumn) ?? 0.0;

            $data[] = [
                'label' => $category->name,
                'sum' => $sum,
                'color' => $category->color,
            ];
        }

        // Sort descending by sum
        usort($data,
            fn (array $a, array $b): int => $b['sum'] <=> $a['sum']
        );

        return [
            'labels' => array_column($data, 'label'),
            'series' => array_column($data, 'sum'),
            'colors' => array_column($data, 'color'),
        ];
    }
}
