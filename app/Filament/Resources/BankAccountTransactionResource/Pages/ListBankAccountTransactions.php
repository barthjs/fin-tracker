<?php

namespace App\Filament\Resources\BankAccountTransactionResource\Pages;

use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use App\Filament\Resources\BankAccountTransactionResource;
use App\Models\TransactionCategory;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListBankAccountTransactions extends ListRecords
{
    protected static string $resource = BankAccountTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('tabler-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'All' => Tab::make(),
            'All Expenses' => Tab::make()
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereType(TransactionType::Expense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Variable Expenses' => Tab::make()
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::VariableExpense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Fixed Expenses' => Tab::make()
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereGroup(TransactionGroup::FixedExpense)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
            'Revenues' => Tab::make()
                ->modifyQueryUsing(function ($query) {
                    $cat = TransactionCategory::whereType(TransactionType::Revenue)->get(['id'])->toArray();
                    $query->whereIn('category_id', $cat);
                }),
        ];
    }
}
