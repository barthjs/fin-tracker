<?php declare(strict_types=1);

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use App\Filament\Exports\TransactionExporter;
use App\Filament\Imports\TransactionImporter;
use App\Filament\Resources\TransactionResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    public function getTitle(): string
    {
        return __('transaction.navigation_label');
    }

    public function getHeading(): string
    {
        return __('transaction.navigation_label');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('transaction.buttons.create_button_label'))
                ->modalHeading(__('transaction.buttons.create_heading')),
            ImportAction::make()
                ->icon('tabler-table-import')
                ->label(__('table.import'))
                ->color('warning')
                ->modalHeading(__('transaction.buttons.import_heading'))
                ->importer(TransactionImporter::class)
                ->failureNotificationTitle(__('transaction.notifications.import.failure_heading'))
                ->successNotificationTitle(__('transaction.notifications.import.success_heading'))
                ->fileRules([File::types(['csv'])->max(1024)]),
            ExportAction::make()
                ->icon('tabler-table-export')
                ->label(__('table.export'))
                ->color('warning')
                ->modalHeading(__('transaction.buttons.export_heading'))
                ->exporter(TransactionExporter::class)
                ->failureNotificationTitle(__('transaction.notifications.export.failure_heading'))
                ->successNotificationTitle(__('transaction.notifications.export.success_heading'))
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->withoutGlobalScopes()->where('user_id', auth()->id()))
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->label(__('table.filter.all')),
            'Expenses' => Tab::make()
                ->label(__('table.filter.expenses'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('type', '=', TransactionType::expense);
                    });
                }),
            'Variable Expenses' => Tab::make()
                ->label(__('category.groups.var_expenses'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('group', '=', TransactionGroup::var_expenses);
                    });
                }),
            'Fixed Expenses' => Tab::make()
                ->label(__('category.groups.fix_expenses'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('group', '=', TransactionGroup::fix_expenses);
                    });
                }),
            'Revenues' => Tab::make()
                ->label(__('table.filter.revenues'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('type', '=', TransactionType::revenue);
                    });
                }),
            'Fixed Revenues' => Tab::make()
                ->label(__('category.groups.fix_revenues'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('group', '=', TransactionGroup::fix_revenues);
                    });
                }),
            'Variable Revenues' => Tab::make()
                ->label(__('category.groups.var_revenues'))
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereHas('category', function ($query) {
                        $query->where('group', '=', TransactionGroup::var_revenues);
                    });
                }),
        ];
    }
}
