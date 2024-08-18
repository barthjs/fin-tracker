<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Filament\Resources\BankAccountResource\RelationManagers\TransactionRelationManager;
use App\Filament\Resources\BankAccountTransactionResource\Pages;
use App\Models\BankAccount;
use App\Models\BankAccountTransaction;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BankAccountTransactionResource extends Resource
{
    protected static ?string $model = BankAccountTransaction::class;
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'tabler-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\DateTimePicker::make('date')
                        ->autofocus()
                        ->default(today())
                        ->required(),
                    Forms\Components\Select::make('bank_account_id')
                        ->relationship('bankAccount', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live(onBlur: true)
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
                    Forms\Components\TextInput::make('amount')
                        ->suffix(fn($get) => BankAccount::whereId($get('bank_account_id'))->first()->currency->value ?? "")
                        ->numeric()
                        ->minValue(-999999999.9999)
                        ->maxValue(999999999.9999)
                        ->inputMode('decimal')
                        ->required(),
                ])->columns(3),
                TransactionRelationManager::descriptionFormPart()
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
                    ->date('Y-m-d H:m')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->fontFamily('mono')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bankAccount.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->suffix(fn($record) => " " . $record->bankAccount->currency->value)
                    ->fontFamily('mono')
                    ->numeric(function ($record) {
                        $numberStr = (string)$record->amount;
                        $decimalPart = substr($numberStr, strpos($numberStr, '.') + 1);
                        $decimalPart = rtrim($decimalPart, '0');
                        $decimalPlaces = strlen($decimalPart);
                        return max($decimalPlaces, 2);
                    })
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(function ($record) {
                        $type = $record->transactionCategory()->first()->type->value;
                        return match (true) {
                            $type == 'Expense' => 'danger',
                            $type == 'Revenue' => 'success',
                            default => 'gray',
                        };
                    }),
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
                Tables\Columns\TextColumn::make('transactionCategory.group')
                    ->label('Group')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->searchable()
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
                Tables\Filters\SelectFilter::make('bankAccount')
                    ->relationship('bankAccount', 'name')
                    ->multiple()
                    ->preload()
                    ->relationship('bankAccount', 'name'),
                SelectFilter::make('category')
                    ->relationship('transactionCategory', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->persistFiltersInSession()
            ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->recordAction(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccountTransactions::route('/'),
            // TODO Replace with modal when bug fixed: https://github.com/filamentphp/filament/issues/12887
            'create' => Pages\CreateBankAccountTransaction::route('/create'),
            'edit' => Pages\EditBankAccountTransaction::route('/{record}/edit'),
        ];
    }
}
