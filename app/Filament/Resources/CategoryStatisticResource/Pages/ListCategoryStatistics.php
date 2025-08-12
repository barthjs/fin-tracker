<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryStatisticResource\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\CategoryStatisticResource;
use App\Filament\Resources\CategoryStatisticResource\Widgets\CategoryStatisticChart;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCategoryStatistics extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CategoryStatisticResource::class;

    public function getTitle(): string
    {
        return __('category_statistic.navigation_label');
    }

    public function getHeading(): string
    {
        return __('category_statistic.navigation_label');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CategoryStatisticChart::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->label(__('table.filter.all')),
            'Expenses' => Tab::make()
                ->icon('tabler-minus')
                ->label(__('table.filter.expenses'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function (Builder $query) {
                        $query->where('type', '=', TransactionType::expense);
                    });
                }),
            'Revenues' => Tab::make()
                ->icon('tabler-plus')
                ->iconPosition('after')
                ->label(__('table.filter.revenues'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function (Builder $query) {
                        $query->where('type', '=', TransactionType::revenue);
                    });
                }),
        ];
    }
}
