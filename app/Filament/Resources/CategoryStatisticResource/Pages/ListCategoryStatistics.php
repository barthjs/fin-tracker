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

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->label(__('bank_account_transaction.filter.all')),
            'Expenses' => Tab::make()
                ->label(__('bank_account_transaction.filter.expenses'))
                ->modifyQueryUsing(function ($query) {
                    $cat = Category::whereType(TransactionType::expense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Revenues' => Tab::make()
                ->label(__('bank_account_transaction.filter.revenues'))
                ->modifyQueryUsing(function ($query) {
                    $cat = Category::whereType(TransactionType::revenue)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
        ];
    }
}
