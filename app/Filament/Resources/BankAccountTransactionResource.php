<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Enums\TransactionGroup;
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BankAccountTransactionResource extends Resource
{
    protected static ?string $model = BankAccountTransaction::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'tabler-credit-card';

    public static function getSlug(): string
    {
        return __('bank_account_transaction.url');
    }

    public static function getNavigationLabel(): string
    {
        return __('bank_account_transaction.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::formParts());
    }

    public static function formParts($account = null, $category = null): array
    {
        // account and category for default values in relation manager
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\DateTimePicker::make('date_time')
                        ->label(__('bank_account_transaction.columns.date'))
                        ->autofocus()
                        ->default(now())
                        ->required(),
                    Forms\Components\Select::make('bank_account_id')
                        ->label(__('bank_account_transaction.columns.account'))
                        ->relationship('bankAccount', 'name')
                        ->default(fn() => $account->id ?? "")
                        ->preload()
                        ->required()
                        ->searchable()
                        ->live(onBlur: true)
                        ->placeholder(__('bank_account_transaction.form.account_placeholder'))
                        ->createOptionForm([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label(__('bank_account.columns.name'))
                                        ->maxLength(255)
                                        ->required()
                                        ->string(),
                                    Forms\Components\Select::make('currency')
                                        ->label(__('bank_account.columns.currency'))
                                        ->placeholder(__('bank_account.form.currency_placeholder'))
                                        ->options(Currency::class)
                                        ->default(fn() => BankAccount::getCurrency())
                                        ->required()
                                        ->searchable(),
                                    Forms\Components\Textarea::make('description')
                                        ->label(__('bank_account.columns.description'))
                                        ->autosize()
                                        ->maxLength(1000)
                                        ->rows(1)
                                        ->string()
                                        ->grow(),
                                    Forms\Components\Toggle::make('active')
                                        ->label(__('table.active'))
                                        ->default(true)
                                        ->inline(false),
                                ])
                                ->columns(4),
                        ]),
                    Forms\Components\TextInput::make('amount')
                        ->label(__('bank_account_transaction.columns.amount'))
                        ->suffix(fn($get) => BankAccount::whereId($get('bank_account_id'))->first()->currency->name ?? "")
                        ->numeric()
                        ->formatStateUsing(function ($state) {
                            // TODO Change format
                            // if value is "1234.0000" display "1234.00"
                            $number = (float)$state;
                            if (floor($number) == $number) {
                                return (string)(int)$number;
                            } else {
                                return (string)$number;
                            }
                        })
                        // Todo set max decimals to 4
                        ->minValue(-999999999.9999)
                        ->maxValue(999999999.9999)
                        ->required(),
                ])
                ->columns(3),
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\TextInput::make('destination')
                        ->label(__('bank_account_transaction.columns.destination'))
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
                        ->label(__('bank_account_transaction.columns.category'))
                        ->relationship('transactionCategory', 'name')
                        ->default(fn() => $category->id ?? "")
                        ->preload()
                        ->required()
                        ->searchable()
                        ->placeholder(__('bank_account_transaction.form.category_placeholder'))
                        ->createOptionForm([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label(__('transaction_category.columns.name'))
                                        ->maxLength(255)
                                        ->required()
                                        ->string(),
                                    Forms\Components\Select::make('group')
                                        ->label(__('transaction_category.columns.group'))
                                        ->placeholder(__('transaction_category.form.group_placeholder'))
                                        ->options(__('transaction_category.groups'))
                                        ->default(TransactionGroup::transfers->name)
                                        ->required(),
                                    Forms\Components\Toggle::make('active')
                                        ->label(__('table.active'))
                                        ->default(true)
                                        ->inline(false),
                                ])
                                ->columns(3)
                        ]),
                    Forms\Components\Textarea::make('notes')
                        ->label(__('bank_account_transaction.columns.notes'))
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
                    ->label(__('bank_account_transaction.columns.date'))
                    ->dateTime('Y-m-d, H:i')
                    ->copyable()
                    ->copyMessage(__('table.copied'))
                    ->fontFamily('mono')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bankAccount.name')
                    ->label(__('bank_account_transaction.columns.account'))
                    ->copyable()
                    ->copyMessage(__('table.copied'))
                    ->searchable()
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
                Tables\Filters\SelectFilter::make('bankAccount')
                    ->label(__('bank_account_transaction.columns.account'))
                    ->relationship('bankAccount', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                SelectFilter::make('category')
                    ->label(__('bank_account_transaction.columns.category'))
                    ->relationship('transactionCategory', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->persistFiltersInSession()
            ->filtersFormColumns(2)
            ->actions(self::getActions())
            ->bulkActions(self::getBulkActions())
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

    public static function getActions(): array
    {
        return [
            Tables\Actions\EditAction::make()
                ->iconButton()
                ->modalHeading(__('bank_account_transaction.buttons.edit_heading'))
                ->using(function (Model $record, array $data): Model {
                    // save old values before updating
                    $oldAccountId = $record->getOriginal('bank_account_id');
                    $oldSum = $record->getOriginal('amount');
                    $record->update($data);

                    // update balance if account has changed
                    if ($oldAccountId != $record->bank_account_id) {
                        $sum = BankAccountTransaction::whereBankAccountId($oldAccountId)->sum('amount');
                        BankAccount::whereId($oldAccountId)->update(['balance' => $sum]);

                        $sum = BankAccountTransaction::whereBankAccountId($record->bank_account_id)->sum('amount');
                        BankAccount::whereId($record->bank_account_id)->update(['balance' => $sum]);
                    } else if ($oldSum !== $record->amount) {
                        // update balance if amount has changed
                        $sum = BankAccountTransaction::whereBankAccountId($record->bank_account_id)->sum('amount');
                        BankAccount::whereId($record->bank_account_id)->update(['balance' => $sum]);
                    }

                    return $record;
                }),
            Tables\Actions\DeleteAction::make()
                ->iconButton()
                ->modalHeading(__('bank_account_transaction.buttons.delete_heading'))
                ->after(function ($record) {
                    $sum = BankAccountTransaction::whereBankAccountId($record->bank_account_id)->sum('amount');
                    BankAccount::whereId($record->bank_account_id)->update(['balance' => $sum]);
                })
        ];
    }

    public static function getBulkActions(): Tables\Actions\BulkActionGroup
    {
        return Tables\Actions\BulkActionGroup::make([
            Tables\Actions\DeleteBulkAction::make()
                ->modalHeading(__('bank_account_transaction.buttons.bulk_delete_heading')),
            Tables\Actions\BulkAction::make('account')
                ->icon('tabler-edit')
                ->label(__('bank_account_transaction.buttons.bulk_account'))
                ->form([
                    Forms\Components\Select::make('bank_account_id')
                        ->label(__('bank_account_transaction.columns.account'))
                        ->placeholder(__('bank_account_transaction.form.account_placeholder'))
                        ->relationship('bankAccount', 'name')
                        ->preload()
                        ->required()
                        ->searchable()
                ])
                ->action(function (Collection $records, array $data): void {
                    // save old values before updating
                    $oldAccountIds = $records->pluck('bank_account_id')->unique();
                    $records->each->update(['bank_account_id' => $data['bank_account_id']]);

                    // update balance for new account
                    $newSum = BankAccountTransaction::whereBankAccountId($data['bank_account_id'])->sum('amount');
                    BankAccount::whereId($data['bank_account_id'])->update(['balance' => $newSum]);

                    // update balance for new accounts
                    foreach ($oldAccountIds as $oldAccountId) {
                        $oldSum = BankAccountTransaction::whereBankAccountId($oldAccountId)->sum('amount');
                        BankAccount::whereId($oldAccountId)->update(['balance' => $oldSum]);
                    }
                })
                ->deselectRecordsAfterCompletion(),
            Tables\Actions\BulkAction::make('category')
                ->icon('tabler-edit')
                ->label(__('bank_account_transaction.buttons.bulk_category'))
                ->form([
                    Forms\Components\Select::make('category_id')
                        ->label(__('bank_account_transaction.columns.category'))
                        ->placeholder(__('bank_account_transaction.form.category_placeholder'))
                        ->relationship('transactionCategory', 'name')
                        ->preload()
                        ->required()
                        ->searchable()
                ])
                ->action(function (Collection $records, array $data): void {
                    $records->each->update(['category_id' => $data['category_id']]);
                })
                ->deselectRecordsAfterCompletion(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccountTransactions::route('/'),
        ];
    }
}
