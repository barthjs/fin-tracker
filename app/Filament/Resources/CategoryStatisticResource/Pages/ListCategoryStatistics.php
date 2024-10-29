<?php

namespace App\Filament\Resources\CategoryStatisticResource\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\CategoryStatisticResource;
use App\Models\Category;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

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
                ->modifyQueryUsing(function ($query) {
                    $cat = Category::whereType(TransactionType::expense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Revenues' => Tab::make()
                ->icon('tabler-plus')
                ->iconPosition('after')
                ->label(__('table.filter.revenues'))
                ->modifyQueryUsing(function ($query) {
                    $cat = Category::whereType(TransactionType::revenue)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
        ];
    }
}
