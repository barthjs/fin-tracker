<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Filament\Resources\TransactionResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form->schema(TransactionResource::formParts(account: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return TransactionResource::table($table)
            ->heading(__('transaction.navigation_label'));
    }

    /**
     * Editable on the list poge
     */
    public function isReadOnly(): bool
    {
        return false;
    }
}
