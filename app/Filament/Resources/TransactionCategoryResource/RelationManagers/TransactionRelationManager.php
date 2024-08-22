<?php

namespace App\Filament\Resources\TransactionCategoryResource\RelationManagers;


use App\Filament\Resources\BankAccountTransactionResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form->schema(BankAccountTransactionResource::formParts(category: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.bank_account_transactions.navigation_label'))
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label(__('resources.bank_account_transactions.table.date'))
                    ->date('Y-m-d H:m')
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->fontFamily('mono')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->fontFamily('mono')
                    ->numeric(function ($record) {
                        $numberStr = (string)$record->amount;
                        $decimalPart = substr($numberStr, strpos($numberStr, '.') + 1);
                        $decimalPart = rtrim($decimalPart, '0');
                        $decimalPlaces = strlen($decimalPart);
                        return max($decimalPlaces, 2);
                    })
                    ->suffix(fn($record) => " " . $record->bankAccount->currency->value)
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(function ($record) {
                        $type = $record->transactionCategory()->first()->type;
                        return match (true) {
                            $type == 'expense' => 'danger',
                            $type == 'income' => 'success',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('bankAccount.name')
                    ->label(__('resources.bank_account_transactions.table.account'))
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('destination')
                    ->label(__('resources.bank_account_transactions.table.destination'))
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('notes')
                    ->label(__('resources.bank_account_transactions.table.notes'))
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->defaultSort('date', 'desc')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('bankAccount')
                    ->label(__('resources.bank_account_transactions.table.account'))
                    ->relationship('bankAccount', 'name')
                    ->multiple()
                    ->preload()
                    ->relationship('bankAccount', 'name'),
            ])
            ->persistFiltersInSession()
            ->emptyStateHeading(__('resources.bank_account_transactions.table.empty'))
            ->emptyStateDescription('')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('resources.bank_account_transactions.create_label'))
                    ->modalHeading(__('resources.bank_account_transactions.create_heading')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()
                    ->modalHeading(__('resources.bank_account_transactions.edit_heading')),
                Tables\Actions\DeleteAction::make()->iconButton()
                    ->modalHeading(__('resources.bank_account_transactions.delete_heading')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->modalHeading(__('resources.bank_account_transactions.bulk_heading')),
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

