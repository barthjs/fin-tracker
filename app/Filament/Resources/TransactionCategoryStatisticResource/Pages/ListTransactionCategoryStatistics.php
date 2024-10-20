<?php

namespace App\Filament\Resources\TransactionCategoryStatisticResource\Pages;

use App\Enums\TransactionType;
use App\Filament\Resources\TransactionCategoryStatisticResource;
use App\Models\TransactionCategory;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTransactionCategoryStatistics extends ListRecords
{
    protected static string $resource = TransactionCategoryStatisticResource::class;

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->label(__('bank_account_transaction.filter.all')),
            'Expenses' => Tab::make()
                ->label(__('bank_account_transaction.filter.expenses'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereType(TransactionType::expense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Revenues' => Tab::make()
                ->label(__('bank_account_transaction.filter.revenues'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereType(TransactionType::revenue)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
        ];
    }
}
