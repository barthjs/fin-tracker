<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryStatisticResource\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\CategoryStatisticResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCategoryStatistics extends ListRecords
{
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

    public function getTabs(): array
    {
        return [
            'Expenses' => Tab::make()
                ->icon('tabler-minus')
                ->label(__('table.filter.expenses'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('type', '=', TransactionType::expense);
                    });
                }),
            'Revenues' => Tab::make()
                ->icon('tabler-plus')
                ->iconPosition('after')
                ->label(__('table.filter.revenues'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('type', '=', TransactionType::revenue);
                    });
                }),
        ];
    }
}
