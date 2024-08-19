<?php

namespace App\Filament\Resources\BankAccountResource\RelationManagers;

use App\Enums\Currency;
use App\Models\BankAccountTransaction;
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
                    Forms\Components\DateTimePicker::make('date')
                        ->label(__('resources.bank_account_transactions.table.date'))
                        ->autofocus()
                        ->default(today())
                        ->required(),
                    Forms\Components\Select::make('bank_account_id')
                        ->label(__('resources.bank_account_transactions.table.account'))
                        ->relationship('bankAccount', 'name')
                        ->default(fn() => $this->getOwnerRecord()->id)
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder(__('resources.bank_account_transactions.form.account_placeholder'))
                        ->createOptionForm([
                            Forms\Components\Section::make()->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('resources.bank_accounts.table.name'))
                                    ->maxLength(255)
                                    ->required()
                                    ->string(),
                                Forms\Components\Select::make('currency')
                                    ->label(__('resources.bank_accounts.table.currency'))
                                    ->placeholder(__('resources.bank_accounts.form.currency_placeholder'))
                                    ->options(Currency::class)
                                    ->required()
                                    ->searchable(),
                                Forms\Components\Toggle::make('active')
                                    ->label(__('tables.active'))
                                    ->default(true)
                                    ->inline(false),
                                Forms\Components\Textarea::make('description')
                                    ->label(__('tables.description'))
                                    ->autosize()
                                    ->columnSpanFull()
                                    ->maxLength(1000)
                                    ->rows(1)
                                    ->string()
                                    ->grow(),
                            ])->columns(3),
                        ]),
                    Forms\Components\TextInput::make('amount')
                        ->label(__('resources.bank_account_transactions.table.amount'))
                        ->suffix(fn() => $this->getOwnerRecord()->currency->value)
                        ->numeric()
                        ->minValue(-999999999.9999)
                        ->maxValue(999999999.9999)
                        ->inputMode('decimal')
                        ->required(),
                ])->columns(3),
                self::descriptionFormPart()
            ]);
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
                    ->label(fn() => __('resources.bank_account_transactions.table.amount_in') . $this->getOwnerRecord()->currency->value)
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
                        $type = $record->transactionCategory()->first()->type;
                        return match (true) {
                            $type == 'expense' => 'danger',
                            $type == 'income' => 'success',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('destination')
                    ->label(__('resources.bank_account_transactions.table.destination'))
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('transactionCategory.name')
                    ->label(__('resources.bank_account_transactions.table.category'))
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('transactionCategory.group')
                    ->label(__('resources.bank_account_transactions.table.group'))
                    ->formatStateUsing(fn($record): string => __('resources.transaction_categories.groups')[$record->transactionCategory->group])
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
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
                SelectFilter::make('name')
                    ->label(__('resources.bank_account_transactions.table.category'))
                    ->relationship('transactionCategory', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
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

    public static function descriptionFormPart(): Forms\Components\Section
    {
        return Forms\Components\Section::make()->schema([
            Forms\Components\TextInput::make('destination')
                ->label(__('resources.bank_account_transactions.table.destination'))
                ->datalist(BankAccountTransaction::query()
                    ->select('destination')
                    ->distinct()
                    ->orderBy('destination')
                    ->pluck('destination')
                    ->toArray())
                ->maxLength(255)
                ->required()
                ->string(),
            Forms\Components\Select::make('category_id')
                ->label(__('resources.bank_account_transactions.table.category'))
                ->relationship('transactionCategory', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->placeholder(__('resources.bank_account_transactions.form.category_placeholder'))
                ->createOptionForm([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('resources.transaction_categories.table.name'))
                            ->autofocus()
                            ->maxLength(255)
                            ->required()
                            ->string(),
                        Forms\Components\Toggle::make('active')
                            ->label(__('tables.active'))
                            ->default(true)
                            ->inline(false),
                        Forms\Components\Select::make('type')
                            ->label(__('resources.transaction_categories.table.type'))
                            ->placeholder(__('resources.transaction_categories.form.type_placeholder'))
                            ->options(__('resources.transaction_categories.types'))
                            ->required(),
                        Forms\Components\Select::make('group')
                            ->label(__('resources.transaction_categories.table.type'))
                            ->placeholder(__('resources.transaction_categories.form.group_placeholder'))
                            ->options(__('resources.transaction_categories.groups'))
                            ->required(),
                    ])->columns(2)
                ]),
            Forms\Components\Textarea::make('notes')
                ->label(__('resources.bank_account_transactions.table.notes'))
                ->autosize()
                ->columnSpanFull()
                ->maxLength(255)
                ->rows(1)
                ->string(),
        ])->columns(2);
    }
}
