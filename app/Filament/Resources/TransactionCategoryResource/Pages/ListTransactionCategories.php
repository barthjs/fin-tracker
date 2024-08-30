<?php

namespace App\Filament\Resources\TransactionCategoryResource\Pages;

use App\Filament\Exports\TransactionCategoryExporter;
use App\Filament\Imports\TransactionCategoryImporter;
use App\Filament\Resources\TransactionCategoryResource;
use App\Models\Scopes\TransactionCategoryScope;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

class ListTransactionCategories extends ListRecords
{
    protected static string $resource = TransactionCategoryResource::class;

    public function getTitle(): string
    {
        return __('transaction_category.navigation_label');
    }

    public function getHeading(): string
    {
        return __('transaction_category.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('transaction_category.buttons.create_button_label'))
                ->modalHeading(__('transaction_category.buttons.create_heading')),
            Actions\ImportAction::make()
                ->icon('tabler-table-import')
                ->label(__('table.import'))
                ->color('warning')
                ->modalHeading(__('transaction_category.buttons.import_heading'))
                ->importer(TransactionCategoryImporter::class)
                ->failureNotificationTitle(__('transaction_category.notifications.import.failure_heading'))
                ->successNotificationTitle(__('transaction_category.notifications.import.success_heading'))
                ->fileRules([
                    File::types(['csv'])->max(1024),
                ]),
            Actions\ExportAction::make()
                ->icon('tabler-table-export')
                ->label(__('table.export'))
                ->color('warning')
                ->modalHeading(__('transaction_category.buttons.export_heading'))
                ->exporter(TransactionCategoryExporter::class)
                ->failureNotificationTitle(__('transaction_category.notifications.export.failure_heading'))
                ->successNotificationTitle(__('transaction_category.notifications.export.success_heading'))
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([TransactionCategoryScope::class])->where('user_id', auth()->id()))
        ];
    }
}
