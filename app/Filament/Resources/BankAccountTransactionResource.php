<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountTransactionResource\Pages;
use App\Filament\Resources\BankAccountTransactionResource\RelationManagers;
use App\Models\BankAccountTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountTransactionResource extends Resource
{
    protected static ?string $model = BankAccountTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('date')
                    ->autofocus()
                    ->default(today())
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('destination')
                    ->string()
                    ->maxLength(255)
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\Select::make('bank_account_id')
                    ->relationship('bankAccount', 'name')
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->relationship('transactionCategory', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('destination')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bankAccount.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transactionCategory.name')
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccountTransactions::route('/'),
            'create' => Pages\CreateBankAccountTransaction::route('/create'),
            'edit' => Pages\EditBankAccountTransaction::route('/{record}/edit'),
        ];
    }
}
