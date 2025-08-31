<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions\Pages;

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Exports\TransactionExporter;
use App\Filament\Imports\TransactionImporter;
use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

final class ListTransactions extends ListRecords
{
    use HasResourceActions;

    protected static string $resource = TransactionResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('table.filter.all')),

            TransactionType::Expense->value => Tab::make()
                ->label(__('table.filter.expenses'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', TransactionType::Expense)),

            CategoryGroup::FixExpenses->value => Tab::make()
                ->label(CategoryGroup::FixExpenses->getLabel())
                ->modifyQueryUsing(function (Builder $query): void {
                    $query->whereHas('category', function (Builder $query): void {
                        $query->where('group', CategoryGroup::FixExpenses);
                    });
                }),

            CategoryGroup::VarExpenses->value => Tab::make()
                ->label(CategoryGroup::VarExpenses->getLabel())
                ->modifyQueryUsing(function (Builder $query): void {
                    $query->whereHas('category', function (Builder $query): void {
                        $query->where('group', CategoryGroup::VarExpenses);
                    });
                }),

            TransactionType::Revenue->value => Tab::make()
                ->label(__('table.filter.revenues'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', TransactionType::Revenue)),

            CategoryGroup::VarRevenues->value => Tab::make()
                ->label(CategoryGroup::VarRevenues->getLabel())
                ->modifyQueryUsing(function (Builder $query): void {
                    $query->whereHas('category', function (Builder $query): void {
                        $query->where('group', CategoryGroup::VarRevenues);
                    });
                }),

            CategoryGroup::FixRevenues->value => Tab::make()
                ->label(CategoryGroup::FixRevenues->getLabel())
                ->modifyQueryUsing(function (Builder $query): void {
                    $query->whereHas('category', function (Builder $query): void {
                        $query->where('group', CategoryGroup::FixRevenues);
                    });
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            self::createAction(),

            self::importAction()
                ->modalHeading(__('transaction.import.modal_heading'))
                ->importer(TransactionImporter::class)
                ->failureNotificationTitle(__('transaction.import.failure_heading'))
                ->successNotificationTitle(__('transaction.import.success_heading'))
                ->fileRules([File::types(['csv'])->max(1024)]),

            self::exportAction()
                ->modalHeading(__('transaction.export.modal_heading'))
                ->exporter(TransactionExporter::class)
                ->failureNotificationTitle(__('transaction.export.failure_heading'))
                ->successNotificationTitle(__('transaction.export.success_heading')),
        ];
    }
}
