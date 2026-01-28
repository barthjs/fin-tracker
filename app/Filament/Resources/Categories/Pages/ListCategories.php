<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Enums\TransactionType;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Exports\CategoryExporter;
use App\Filament\Imports\CategoryImporter;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListCategories extends ListRecords
{
    use HasResourceActions;

    protected static string $resource = CategoryResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('table.filter.all')),

            TransactionType::Expense->value => Tab::make()
                ->icon('tabler-minus')
                ->iconPosition('before')
                ->label(__('table.filter.expenses'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', TransactionType::Expense)),

            TransactionType::Revenue->value => Tab::make()
                ->icon('tabler-plus')
                ->iconPosition('after')
                ->label(__('table.filter.revenues'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', TransactionType::Revenue)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            self::createAction(),

            self::importAction()
                ->modalHeading(__('category.import.modal_heading'))
                ->importer(CategoryImporter::class)
                ->failureNotificationTitle(__('category.import.failure_heading'))
                ->successNotificationTitle(__('category.import.success_heading')),

            self::exportAction()
                ->modalHeading(__('category.export.modal_heading'))
                ->exporter(CategoryExporter::class)
                ->failureNotificationTitle(__('category.export.failure_heading'))
                ->successNotificationTitle(__('category.export.success_heading')),
        ];
    }
}
