<?php

namespace App\Filament\Resources\BankAccountResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\DatePicker::make('date')
                        ->autofocus()
                        ->default(today())
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->suffix(fn() => $this->getOwnerRecord()->currency->value)
                        ->numeric()
                        ->inputMode('decimal')
                        ->required(),
                    Forms\Components\TextInput::make('destination')
                        ->maxLength(255)
                        ->required()
                        ->string(),
                ])->columns(3),
                Forms\Components\Section::make()->schema([
                    Forms\Components\Select::make('bank_account_id')
                        ->relationship('bankAccount', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->string()
                                ->required()
                                ->maxLength(255),
                        ]),
                    Forms\Components\Select::make('category_id')
                        ->relationship('transactionCategory', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
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
                    Forms\Components\Textarea::make('notes')
                        ->autosize()
                        ->columnSpanFull()
                        ->maxLength(255)
                        ->rows(1)
                        ->string(),
                ])->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('Y.m.d')
                    ->fontFamily('mono')
                    ->sortable(),
                Tables\Columns\TextColumn::make('destination')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('transactionCategory.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(fn() => 'Amount in ' . $this->getOwnerRecord()->currency->value)
                    ->fontFamily('mono')
                    ->numeric(2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->defaultSort('date', 'desc')
            ->persistSortInSession()
            ->striped()
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ]);
    }

    /**
     * Editable on the view poge
     */
    public function isReadOnly(): bool
    {
        return false;
    }
}
