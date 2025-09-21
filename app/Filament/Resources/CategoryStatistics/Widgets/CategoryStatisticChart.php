<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryStatistics\Widgets;

use App\Enums\TransactionType;
use App\Filament\Resources\CategoryStatistics\Pages\ListCategoryStatistics;
use App\Models\CategoryStatistic;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Illuminate\Database\Eloquent\Collection;

final class CategoryStatisticChart extends ChartWidget
{
    use InteractsWithPageTable;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = null;

    protected bool $isCollapsible = true;

    protected function getTablePage(): string
    {
        return ListCategoryStatistics::class;
    }

    protected function getData(): array
    {
        /** @var Collection<int, CategoryStatistic> $records */
        $records = $this->getPageTableRecords();

        $sums = [
            TransactionType::Expense->value => array_fill_keys(CategoryStatistic::MONTHS, 0.0),
            TransactionType::Revenue->value => array_fill_keys(CategoryStatistic::MONTHS, 0.0),
        ];

        foreach ($records as $record) {
            $type = $record->category->type->value;

            foreach (CategoryStatistic::MONTHS as $month) {
                $value = (float) $record->$month;

                if ($type === TransactionType::Expense->value) {
                    $sums[TransactionType::Expense->value][$month] -= $value;
                } elseif ($type === TransactionType::Revenue->value) {
                    $sums[TransactionType::Revenue->value][$month] += $value;
                }
            }
        }

        $datasets = [];

        foreach ($sums as $type => $values) {
            $datasets[] = [
                'label' => __('table.filter.'.($type === TransactionType::Expense->value ? 'expenses' : 'revenues')),
                'data' => array_values($values),
                'backgroundColor' => $type === TransactionType::Expense->value ? '#f87171' : '#44c975',
                'borderColor' => $type === TransactionType::Expense->value ? '#f87171' : '#44c975',
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => array_values(__('category_statistic.fields')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
