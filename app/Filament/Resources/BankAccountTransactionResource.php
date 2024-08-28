<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
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
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'tabler-credit-card';

    public static function getNavigationLabel(): string
    {
        return __('resources.bank_account_transactions.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::formParts());
    }

    public static function formParts($account = null, $category = null): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\DateTimePicker::make('date_time')
                        ->label(__('resources.bank_account_transactions.table.date'))
                        ->autofocus()
                        ->default(today())
                        ->required(),
                    Forms\Components\Select::make('bank_account_id')
                        ->label(__('resources.bank_account_transactions.table.account'))
                        ->relationship('bankAccount', 'name')
                        ->default(fn() => $account->id ?? "")
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live(onBlur: true)
                        ->placeholder(__('resources.bank_account_transactions.form.account_placeholder'))
                        ->createOptionForm([
                            Forms\Components\Section::make()
                                ->schema([
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
                                ])
                                ->columns(3),
                        ]),
                    Forms\Components\TextInput::make('amount')
                        ->label(__('resources.bank_account_transactions.table.amount'))
                        ->suffix(fn($get) => $account->currency->value ?? BankAccount::whereId($get('bank_account_id'))->first()->currency->value ?? "")
                        ->numeric()
                        ->minValue(-999999999.9999)
                        ->maxValue(999999999.9999)
                        ->inputMode('decimal')
                        ->required(),
                ])
                ->columns(3),
            Forms\Components\Section::make()
                ->schema([
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
                        ->default(fn() => $category->id ?? "")
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder(__('resources.bank_account_transactions.form.category_placeholder'))
                        ->createOptionForm([
                            Forms\Components\Section::make()
                                ->schema([
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
                                ])
                                ->columns(2)
                        ]),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('resources.bank_account_transactions.table.notes'))
                        ->autosize()
                        ->columnSpanFull()
                        ->maxLength(255)
                        ->rows(1)
                        ->string(),
                ])
                ->columns(2)
        ];
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date_time')
                    ->label(__('resources.bank_account_transactions.table.date'))
                    ->dateTime('Y-m-d H:m')
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->fontFamily('mono')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bankAccount.name')
                    ->label(__('resources.bank_account_transactions.table.account'))
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('resources.bank_account_transactions.table.amount'))
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
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
                        $type = $record->transactionCategory()->first()->type->name;
                        return match (true) {
                            $type == 'expense' => 'danger',
                            $type == 'revenue' => 'success',
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
                Tables\Columns\TextColumn::make('notes')
                    ->label(__('resources.bank_account_transactions.table.notes'))
                    ->copyable()
                    ->copyMessage(__('tables.copied'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->defaultSort('date_time', 'desc')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('bankAccount')
                    ->label(__('resources.bank_account_transactions.table.account'))
                    ->relationship('bankAccount', 'name')
                    ->multiple()
                    ->preload()
                    ->relationship('bankAccount', 'name'),
                SelectFilter::make('category')
                    ->label(__('resources.bank_account_transactions.table.category'))
                    ->relationship('transactionCategory', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->persistFiltersInSession()
            ->emptyStateHeading(__('resources.bank_account_transactions.table.empty'))
            ->filtersFormColumns(2)
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
