<?php

namespace App\Filament\Resources\BankAccountResource\RelationManagers;

use App\Filament\Resources\BankAccountTransactionResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form->schema(BankAccountTransactionResource::formParts(account: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return BankAccountTransactionResource::table($table)
            ->heading(__('bank_account_transaction.navigation_label'));
    }

    /**
     * Editable on the list poge
     */
    public function isReadOnly(): bool
    {
        return false;
    }
}
