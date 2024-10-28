<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Exports\CategoryExporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Resources\CategoryResource;
use App\Models\Scopes\CategoryScope;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

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
                ->importer(CategoryImporter::class)
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
                ->exporter(CategoryExporter::class)
                ->failureNotificationTitle(__('transaction_category.notifications.export.failure_heading'))
                ->successNotificationTitle(__('transaction_category.notifications.export.success_heading'))
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([CategoryScope::class])->where('user_id', auth()->id()))
        ];
    }
}
