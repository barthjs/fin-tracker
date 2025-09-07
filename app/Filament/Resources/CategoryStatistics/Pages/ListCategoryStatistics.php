<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryStatistics\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\CategoryStatistics\CategoryStatisticResource;
use App\Filament\Resources\CategoryStatistics\Widgets\CategoryStatisticChart;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListCategoryStatistics extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CategoryStatisticResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('table.filter.all')),

            TransactionType::Expense->value => Tab::make()
                ->icon('tabler-minus')
                ->label(__('table.filter.expenses'))
                ->modifyQueryUsing(function (Builder $query): void {
                    $query->whereHas('category', function (Builder $query): void {
                        $query->where('type', TransactionType::Expense);
                    });
                }),

            TransactionType::Revenue->value => Tab::make()
                ->icon('tabler-plus')
                ->iconPosition('after')
                ->label(__('table.filter.revenues'))
                ->modifyQueryUsing(function (Builder $query): void {
                    $query->whereHas('category', function (Builder $query): void {
                        $query->where('type', TransactionType::Revenue);
                    });
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CategoryStatisticChart::class,
        ];
    }
}
