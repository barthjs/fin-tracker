<?php

namespace App\Filament\Resources\BankAccountResource\RelationManagers;

use App\Filament\Resources\BankAccountTransactionResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
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
        return $table
            ->heading(__('bank_account_transaction.navigation_label'))
            ->columns([
                Tables\Columns\TextColumn::make('date_time')
                    ->label(__('bank_account_transaction.columns.date'))
                    ->date('Y-m-d H:m')
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->fontFamily('mono')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('bank_account_transaction.columns.amount'))
                    ->copyable()
                    ->copyMessage(__('table.copied'))
                    ->fontFamily('mono')
                    ->numeric(function ($state) {
                        $numberStr = (string)$state;
                        $decimalPart = rtrim(substr($numberStr, strpos($numberStr, '.') + 1), '0');
                        return max(strlen($decimalPart), 2);
                    })
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn($record) => match ($record->transactionCategory->type->name) {
                        'expense' => 'danger',
                        'revenue' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('destination')
                    ->label(__('bank_account_transaction.columns.destination'))
                    ->copyable()
                    ->copyMessage(__('table.copied'))
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('transactionCategory.name')
                    ->label(__('bank_account_transaction.columns.category'))
                    ->copyable()
                    ->copyMessage(__('table.copied'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('transactionCategory.group.name')
                    ->label(__('bank_account_transaction.columns.group'))
                    ->formatStateUsing(fn($state): string => __('transaction_category.groups')[$state])
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label(__('bank_account_transaction.columns.notes'))
                    ->copyable()
                    ->copyMessage(__('table.copied'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->defaultSort('date_time', 'desc')
            ->persistSortInSession()
            ->striped()
            ->filters([
                SelectFilter::make('category')
                    ->label(__('bank_account_transaction.columns.category'))
                    ->relationship('transactionCategory', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->persistFiltersInSession()
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('bank_account_transaction.buttons.create_button_label'))
                    ->modalHeading(__('bank_account_transaction.buttons.create_heading')),
            ])
            ->actions(BankAccountTransactionResource::getActions())
            ->bulkActions(BankAccountTransactionResource::getBulkActions())
            ->emptyStateHeading(__('bank_account_transaction.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('bank_account_transaction.buttons.create_button_label'))
                    ->modalHeading(__('bank_account_transaction.buttons.create_heading')),
            ])
            ->recordAction(null);
    }

    /**
     * Editable on the list poge
     */
    public function isReadOnly(): bool
    {
        return false;
    }
}
