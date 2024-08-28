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
        return __('resources.bank_account_transactions.navigation_label');
    }

    public function getHeading(): string
    {
        return __('resources.bank_account_transactions.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('resources.bank_account_transactions.create_label')),
            Actions\ImportAction::make()
                ->label('import')
                ->importer(BankAccountTransactionImporter::class)
                ->fileRules([
                    File::types(['csv'])->max(1024),
                ]),
            Actions\ExportAction::make()
                ->exporter(BankAccountTransactionExporter::class)
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
                ->label(__('resources.bank_account_transactions.filter.all')),
            'All Expenses' => Tab::make()
                ->label(__('resources.bank_account_transactions.filter.expenses'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereType(TransactionType::expense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Variable Expenses' => Tab::make()
                ->label(__('resources.bank_account_transactions.filter.var_expenses'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::var_expenses)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Fixed Expenses' => Tab::make()
                ->label(__('resources.bank_account_transactions.filter.fix_expenses'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::fix_expenses)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Revenues' => Tab::make()
                ->label(__('resources.bank_account_transactions.filter.revenues'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereType(TransactionType::revenue)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Fixed Revenues' => Tab::make()
                ->label(__('resources.bank_account_transactions.filter.fix_revenues'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::fix_revenues)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Variable Revenues' => Tab::make()
                ->label(__('resources.bank_account_transactions.filter.var_revenues'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::var_revenues)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
        ];
    }
}
