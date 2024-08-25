<?php

namespace App\Filament\Resources\BankAccountTransactionResource\Pages;

use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use App\Filament\Imports\BankAccountTransactionImporter;
use App\Filament\Resources\BankAccountTransactionResource;
use App\Models\TransactionCategory;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

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
                    $cat = TransactionCategory::whereGroup(TransactionGroup::var_expense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Fixed Expenses' => Tab::make()
                ->label(__('resources.bank_account_transactions.filter.fix_expenses'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::fix_expense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Revenues' => Tab::make()
                ->label(__('resources.bank_account_transactions.filter.rev'))
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereType(TransactionType::income)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
        ];
    }
}
