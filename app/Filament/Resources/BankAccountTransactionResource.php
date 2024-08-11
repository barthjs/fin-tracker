<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountTransactionResource\Pages;
use App\Models\BankAccountTransaction;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountTransactionResource extends Resource
{
    protected static ?string $model = BankAccountTransaction::class;
    protected static ?int $navigationSort = 1;

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
                    ->maxLength(255)
                    ->required()
                    ->string(),
                Forms\Components\Textarea::make('notes'),
                Forms\Components\Select::make('bank_account_id')
                    ->relationship('bankAccount', 'name')
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->maxLength(255)
                            ->required()
                            ->string()
                    ]),
                Forms\Components\Select::make('category_id')
                    ->relationship('transactionCategory', 'name')
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->maxLength(255)
                            ->required()
                            ->string(),
                        Forms\Components\TextInput::make('type')
                            ->maxLength(255)
                            ->required()
                            ->string(),
                    ]),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('Y.m.d')
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
                Tables\Filters\SelectFilter::make('name')
                    ->multiple()
                    ->preload()
                    ->relationship('bankAccount', 'name')
            ])
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
