<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Enums\TransactionType;
use App\Filament\Exports\CategoryExporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Resources\CategoryResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string
    {
        return __('category.navigation_label');
    }

    public function getHeading(): string
    {
        return __('category.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('category.buttons.create_button_label'))
                ->modalHeading(__('category.buttons.create_heading')),
            ImportAction::make()
                ->icon('tabler-table-import')
                ->label(__('table.import'))
                ->color('warning')
                ->modalHeading(__('category.buttons.import_heading'))
                ->importer(CategoryImporter::class)
                ->failureNotificationTitle(__('category.notifications.import.failure_heading'))
                ->successNotificationTitle(__('category.notifications.import.success_heading'))
                ->fileRules([File::types(['csv'])->max(1024)]),
            ExportAction::make()
                ->icon('tabler-table-export')
                ->label(__('table.export'))
                ->color('warning')
                ->modalHeading(__('category.buttons.export_heading'))
                ->exporter(CategoryExporter::class)
                ->failureNotificationTitle(__('category.notifications.export.failure_heading'))
                ->successNotificationTitle(__('category.notifications.export.success_heading'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes()->where('user_id', auth()->id())),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->label(__('table.filter.all')),
            'Expenses' => Tab::make()
                ->icon('tabler-minus')
                ->iconPosition('before')
                ->label(__('table.filter.expenses'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('type', '=', TransactionType::expense);
                }),
            'Revenues' => Tab::make()
                ->icon('tabler-plus')
                ->iconPosition('after')
                ->label(__('table.filter.revenues'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('type', '=', TransactionType::revenue);
                }),
        ];
    }
}
