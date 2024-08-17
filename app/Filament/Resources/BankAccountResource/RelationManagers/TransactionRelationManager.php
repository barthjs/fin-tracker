<?php

namespace App\Filament\Resources\BankAccountResource\RelationManagers;

use App\Enums\Currency;
use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use Exception;
use Filament\Forms;
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
                            Forms\Components\Section::make()->schema([
                                Forms\Components\TextInput::make('name')
                                    ->maxLength(255)
                                    ->required()
                                    ->string(),
                                Forms\Components\Select::make('currency')
                                    ->options(Currency::class)
                                    ->required()
                                    ->searchable(),
                                Forms\Components\Toggle::make('active')
                                    ->default(true)
                                    ->inline(false),
                                Forms\Components\Textarea::make('description')
                                    ->autosize()
                                    ->columnSpanFull()
                                    ->maxLength(1000)
                                    ->rows(1)
                                    ->string()
                                    ->grow(),
                            ])->columns(3),
                        ]),
                    Forms\Components\Select::make('category_id')
                        ->relationship('transactionCategory', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\Section::make()->schema([
                                Forms\Components\TextInput::make('name')
                                    ->autofocus()
                                    ->maxLength(255)
                                    ->required()
                                    ->string(),
                                Forms\Components\Toggle::make('active')
                                    ->default(true)
                                    ->inline(false),
                                Forms\Components\Select::make('type')
                                    ->options(TransactionType::class)
                                    ->required(),
                                Forms\Components\Select::make('group')
                                    ->options(TransactionGroup::class)
                                    ->required(),
                            ])->columns(2)
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

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('Y.m.d')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->fontFamily('mono')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('destination')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('transactionCategory.name')
                    ->label('Category')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('transactionCategory.type')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->label('Type')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('transactionCategory.group')
                    ->label('Group')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->label(fn() => 'Amount in ' . $this->getOwnerRecord()->currency->value)
                    ->fontFamily('mono')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('notes')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->defaultSort('date', 'desc')
            ->persistSortInSession()
            ->striped()
            ->filters([
                SelectFilter::make('name')
                    ->relationship('transactionCategory', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                SelectFilter::make('type')
                    ->options(TransactionType::class)
                    ->multiple(),
                SelectFilter::make('group')
                    ->options(TransactionGroup::class)
                    ->multiple()
            ])
            ->persistFiltersInSession()
            ->filtersFormColumns(3)
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
