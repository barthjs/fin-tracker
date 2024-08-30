<?php

namespace App\Filament\Resources\TransactionCategoryResource\Pages;

use App\Filament\Resources\TransactionCategoryResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTransactionCategory extends ViewRecord
{
    protected static string $resource = TransactionCategoryResource::class;

    public function getTitle(): string
    {
        return  __('transaction_category.navigation_label');
    }

    public function getHeading(): string
    {
        return __('');
    }
}
