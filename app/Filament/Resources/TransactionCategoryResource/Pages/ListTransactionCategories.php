<?php

namespace App\Filament\Resources\TransactionCategoryResource\Pages;

use App\Filament\Exports\TransactionCategoryExporter;
use App\Filament\Imports\TransactionCategoryImporter;
use App\Filament\Resources\TransactionCategoryResource;
use App\Models\Scopes\TransactionCategoryScope;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

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
                ->modalHeading(__('resources.transaction_categories.create_heading')),
            Actions\ImportAction::make()
                ->label('import')
                ->importer(TransactionCategoryImporter::class),
            Actions\ExportAction::make()
                ->exporter(TransactionCategoryExporter::class)
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([TransactionCategoryScope::class])->where('user_id', auth()->id()))
        ];
    }
}
