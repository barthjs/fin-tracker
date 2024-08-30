<?php

namespace App\Filament\Resources\BankAccountTransactionResource\Pages;

use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use App\Filament\Exports\BankAccountTransactionExporter;
use App\Filament\Imports\BankAccountTransactionImporter;
use App\Filament\Resources\BankAccountTransactionResource;
use App\Models\BankAccount;
use App\Models\Scopes\BankAccountScope;
use App\Models\Scopes\BankAccountTransactionScope;
use App\Models\Scopes\TransactionCategoryScope;
use App\Models\TransactionCategory;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

class ListBankAccountTransactions extends ListRecords
{
    protected static string $resource = BankAccountTransactionResource::class;

    public function getTitle(): string
    {
        return __('bank_account_transaction.navigation_label');
    }

    public function getHeading(): string
    {
        return __('bank_account_transaction.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('bank_account_transaction.buttons.create_button_label')),
            Actions\ImportAction::make()
                ->icon('tabler-table-import')
                ->label(__('table.import'))
                ->color('warning')
                ->importer(BankAccountTransactionImporter::class)
                ->modalHeading(__('bank_account_transaction.buttons.import_heading'))
                ->failureNotificationTitle(__('bank_account_transaction.notifications.import.failure_heading'))
                ->successNotificationTitle(__('bank_account_transaction.notifications.import.success_heading'))
                ->fileRules([
                    File::types(['csv'])->max(1024),
                ]),
            Actions\ExportAction::make()
                ->icon('tabler-table-export')
                ->label(__('table.export'))
                ->color('warning')
                ->modalHeading(__('bank_account_transaction.buttons.export_heading'))
                ->exporter(BankAccountTransactionExporter::class)
                ->failureNotificationTitle(__('bank_account_transaction.notifications.export.failure_heading'))
                ->successNotificationTitle(__('bank_account_transaction.notifications.export.success_heading'))
                ->modifyQueryUsing(function (Builder $query) {
                    // Todo improve this
                    $bankAccounts = BankAccount::whereUserId(auth()->id())->pluck('id')->toArray();
                    return $query->withoutGlobalScopes([BankAccountScope::class, BankAccountTransactionScope::class, TransactionCategoryScope::class])
                        ->whereIn('bank_account_id', $bankAccounts);
                })
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make()
                ->label(__('bank_account_transaction.filter.all')),
            'All Expenses' => Tab::make()
                ->label(__('bank_account_transaction.filter.expenses'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereType(TransactionType::expense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Variable Expenses' => Tab::make()
                ->label(__('bank_account_transaction.filter.var_expenses'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::var_expenses)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Fixed Expenses' => Tab::make()
                ->label(__('bank_account_transaction.filter.fix_expenses'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::fix_expenses)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Revenues' => Tab::make()
                ->label(__('bank_account_transaction.filter.revenues'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereType(TransactionType::revenue)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Fixed Revenues' => Tab::make()
                ->label(__('bank_account_transaction.filter.fix_revenues'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::fix_revenues)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Variable Revenues' => Tab::make()
                ->label(__('bank_account_transaction.filter.var_revenues'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::var_revenues)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
        ];
    }
}
