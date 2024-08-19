<?php

namespace App\Filament\Resources\TransactionCategoryResource\Pages;

use App\Filament\Resources\TransactionCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactionCategories extends ListRecords
{
    protected static string $resource = TransactionCategoryResource::class;

    public function getTitle(): string
    {
        return __('resources.transaction_categories.navigation_label');
    }

    public function getHeading(): string
    {
        return __('resources.transaction_categories.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('resources.transaction_categories.create_label'))
                ->modalHeading(__('resources.transaction_categories.create_heading'))
        ];
    }
}
