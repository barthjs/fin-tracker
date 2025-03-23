<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryStatisticResource\Widgets;

use App\Enums\TransactionType;
use App\Filament\Resources\CategoryStatisticResource\Pages\ListCategoryStatistics;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;

class CategoryStatisticChart extends ChartWidget
{
    use InteractsWithPageTable;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListCategoryStatistics::class;
    }

    protected function getData(): array
    {
        $data = $this->getPageTableRecords();
        $query = $this->getPageTableQuery();
        $type = $query->getQuery()->bindings['where'][0] ?? null;

        $months = array_keys(__('category_statistic.columns'));

        $expensesSum = array_fill_keys($months, 0);
        $revenuesSum = array_fill_keys($months, 0);

        foreach ($data as $record) {
            $categoryType = $record->category->type;

            foreach ($months as $month) {
                if ($categoryType === TransactionType::expense) {
                    $expensesSum[$month] += $record->$month ?? 0;
                } elseif ($categoryType === TransactionType::revenue) {
                    $revenuesSum[$month] += $record->$month ?? 0;
                }
            }
        }

        $datasets = [];

        if ($type === 'expense') {
            $datasets[] = [
                'label' => __('table.filter.expenses'),
                'data' => array_values($expensesSum),
                'backgroundColor' => '#f87171',
                'borderColor' => '#f87171',
            ];
        } elseif ($type === 'revenue') {
            $datasets[] = [
                'label' => __('table.filter.revenues'),
                'data' => array_values($revenuesSum),
                'backgroundColor' => '#44c975',
                'borderColor' => '#44c975',
            ];
        } else {
            $datasets[] = [
                'label' => __('table.filter.expenses'),
                'data' => array_values($expensesSum),
                'backgroundColor' => '#f87171',
                'borderColor' => '#f87171',
            ];
            $datasets[] = [
                'label' => __('table.filter.revenues'),
                'data' => array_values($revenuesSum),
                'backgroundColor' => '#44c975',
                'borderColor' => '#44c975',
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => array_map('ucfirst', array_values(__('category_statistic.columns'))),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
