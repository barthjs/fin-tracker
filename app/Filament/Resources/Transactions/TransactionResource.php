<?php

declare(strict_types=1);

namespace App\Filament\Resources\Transactions;

use App\Enums\TransactionType;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Accounts;
use App\Filament\Resources\Categories;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TransactionResource extends Resource
{
    use HasResourceActions, HasResourceFormFields, HasResourceTableColumns;

    protected static ?string $model = Transaction::class;

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-credit-card';

    public static function getModelLabel(): string
    {
        return __('transaction.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('transaction.plural_label');
    }

    /**
     * Account and category for default values in relation manager.
     */
    public static function form(Schema $schema, Account|Model|null $account = null, Category|Model|null $category = null): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    self::dateTimePickerField(),

                    self::accountSelectField()
                        ->getOptionLabelUsing(fn (?string $value): ?string => Account::find($value)?->name)
                        ->default(fn (): ?string => $account instanceof Account ? $account->id : null)
                        ->live(true),

                    TextInput::make('amount')
                        ->label(__('transaction.fields.amount'))
                        ->suffix(fn (Get $get): ?string => Account::find($get('account_id'))?->currency?->value)
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0.0)
                        ->maxValue(1e9)
                        ->required(),

                    self::typeSelectField()
                        ->options(TransactionType::class)
                        ->default(TransactionType::Expense),

                    Select::make('transfer_account_id')
                        ->label(__('account.fields.transfer_account_id'))
                        ->columnSpanFull()
                        ->visible(fn (Get $get): bool => $get('type') === TransactionType::Transfer)
                        ->required(fn (Get $get): bool => $get('type') === TransactionType::Transfer)
                        ->selectablePlaceholder(false)
                        ->options(fn (Get $get): array => Account::query()
                            ->where('is_active', true)
                            ->when($get('account_id'), fn (Builder $query, mixed $id): Builder => $query->whereKeyNot($id))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()
                        )
                        ->searchable()
                        ->preload(),
                ]),

            Section::make()
                ->schema([
                    TextInput::make('payee')
                        ->label(__('transaction.fields.payee'))
                        ->datalist(fn (): array => Transaction::query()
                            ->whereBetween('date_time', [today()->subYear(), today()])
                            ->distinct()
                            ->orderBy('payee')
                            ->pluck('payee')
                            ->toArray()
                        )
                        ->required()
                        ->maxLength(255),

                    self::categorySelectField()
                        ->default(fn (): ?string => $category instanceof Category ? $category->id : null),

                    self::notesField(),
                ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->modelLabel(__('transaction.label'))
            ->pluralModelLabel(__('transaction.plural_label'))
            ->columns([
                self::dateTimeColumn('date_time'),

                TextColumn::make('amount')
                    ->label(__('transaction.fields.amount'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->badge()
                    ->color(fn (Transaction $record): string => $record->type->getColor())
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('payee')
                    ->label(__('transaction.fields.payee'))
                    ->wrap()
                    ->searchable()
                    ->toggleable(),

                self::logoAndNameColumn('account.name')
                    ->hiddenOn(Accounts\RelationManagers\TransactionRelationManager::class)
                    ->label(Str::ucfirst(__('account.label')))
                    ->state(fn (Transaction $record): array => [
                        'logo' => $record->account->logo,
                        'name' => $record->account->name,
                    ])
                    ->toggleable(),

                TextColumn::make('category.name')
                    ->hiddenOn(Categories\RelationManagers\TransactionRelationManager::class)
                    ->label(Str::ucfirst(__('category.label')))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                self::notesColumn(),
            ])
            ->defaultSort('date_time', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->hiddenOn(ListTransactions::class)
                    ->label(__('fields.type'))
                    ->options(TransactionType::class),

                SelectFilter::make('account_id')
                    ->hiddenOn(Accounts\RelationManagers\TransactionRelationManager::class)
                    ->label(Str::ucfirst(__('account.label')))
                    ->relationship('account', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('category_id')
                    ->hiddenOn(Categories\RelationManagers\TransactionRelationManager::class)
                    ->label(Str::ucfirst(__('category.label')))
                    ->relationship('category', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
                    ->multiple()
                    ->preload()
                    ->searchable(),

                Filter::make('date')
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('table.filter.created_from'))
                            ->default(Carbon::today()->startOfYear()),

                        DatePicker::make('until')
                            ->label(__('table.filter.created_until')),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        /** @var array{from: string, until: string} $data */
                        return $query
                            ->when($data['from'],
                                fn (Builder $query, string $date): Builder => $query->whereDate('date_time', '>=', $date))
                            ->when($data['until'],
                                fn (Builder $query, string $date): Builder => $query->whereDate('date_time', '<=', $date));
                    }),
            ], FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->headerActions([
                self::createAction()
                    ->hidden(fn (mixed $livewire = null): bool => $livewire instanceof ListTransactions)
                    /** @phpstan-ignore-next-line */
                    ->using(fn (array $data): Transaction => Transaction::create($data)),
            ])
            ->recordActions(self::getActions())
            ->toolbarActions(self::getBulkActions());
    }

    /**
     * @return array<int, Action>
     */
    public static function getActions(): array
    {
        return [
            self::tableEditAction()
                ->using(function (Transaction $record, array $data): Transaction {
                    /** @var array<string, mixed> $data */

                    /** @var CarbonInterface $oldDate */
                    $oldDate = $record->getOriginal('date_time');
                    /** @var float $oldAmount */
                    $oldAmount = $record->getOriginal('amount');
                    /** @var string $oldAccountId */
                    $oldAccountId = $record->getOriginal('account_id');
                    /** @var string $oldCategoryId */
                    $oldCategoryId = $record->getOriginal('category_id');
                    /** @var string|null $oldTransferAccountId */
                    $oldTransferAccountId = $record->getOriginal('transfer_account_id');
                    /** @var TransactionType $oldType */
                    $oldType = $record->getOriginal('type');

                    DB::transaction(function () use ($record, $data, $oldDate, $oldAmount, $oldAccountId, $oldCategoryId, $oldTransferAccountId, $oldType) {
                        $record->update($data);

                        $amount = $record->amount;
                        $date = $record->date_time;
                        $accountId = $record->account_id;
                        $categoryId = $record->category_id;
                        $type = $record->type;
                        $transferAccountId = $record->transfer_account_id;

                        $accountsToUpdate = [];

                        if ($oldAccountId !== $accountId) {
                            $accountsToUpdate[] = $oldAccountId;
                            $accountsToUpdate[] = $accountId;
                        } elseif ($oldAmount !== $amount || $oldType !== $type) {
                            $accountsToUpdate[] = $accountId;
                        }

                        // Handle transfer target accounts
                        $wasTransfer = $oldType === TransactionType::Transfer;
                        $isTransfer = $type === TransactionType::Transfer;

                        if ($wasTransfer && $oldTransferAccountId) {
                            $accountsToUpdate[] = $oldTransferAccountId;
                        }
                        if ($isTransfer && $transferAccountId) {
                            $accountsToUpdate[] = $transferAccountId;
                        }

                        foreach (array_unique(array_filter($accountsToUpdate)) as $id) {
                            Account::updateAccountBalance($id);
                        }

                        if ($oldCategoryId !== $categoryId || $date->notEqualTo($oldDate) || $oldAmount !== $amount || $oldType !== $type) {
                            Transaction::updateCategoryStatistics($oldCategoryId, $oldDate);
                            Transaction::updateCategoryStatistics($categoryId, $date);
                        }
                    });

                    return $record;
                }),

            self::tableDeleteAction()
                ->after(function (Transaction $record): Transaction {
                    Account::updateAccountBalance($record->account_id);
                    if ($record->type === TransactionType::Transfer && $record->transfer_account_id !== null) {
                        Account::updateAccountBalance($record->transfer_account_id);
                    }
                    Transaction::updateCategoryStatistics($record->category_id, $record->date_time);

                    return $record;
                }),
        ];
    }

    public static function getBulkActions(): BulkActionGroup
    {
        return BulkActionGroup::make([
            DeleteBulkAction::make()
                ->icon('tabler-trash')
                ->after(function (Collection $records): void {
                    /** @var Collection<int, Transaction> $records */
                    $accountIds = [];
                    $transferAccountIds = [];
                    $categories = [];

                    foreach ($records as $record) {
                        $accountIds[] = $record->account_id;
                        if ($record->type === TransactionType::Transfer && $record->transfer_account_id !== null) {
                            $transferAccountIds[] = $record->transfer_account_id;
                        }
                        $categories[] = [$record->category_id, $record->date_time];
                    }

                    foreach (array_unique($accountIds) as $accountId) {
                        Account::updateAccountBalance($accountId);
                    }

                    foreach (array_unique($transferAccountIds) as $accountId) {
                        Account::updateAccountBalance($accountId);
                    }

                    foreach ($categories as [$categoryId, $date]) {
                        Transaction::updateCategoryStatistics($categoryId, $date);
                    }
                }),

            BulkAction::make('account_id')
                ->icon('tabler-edit')
                ->label(__('account.buttons.bulk_edit_account'))
                ->schema([
                    self::accountSelectField(),
                ])
                ->action(function (Collection $records, array $data): void {
                    /**
                     * Save old values before updating
                     *
                     * @var Collection<int, Transaction> $records
                     * @var array<string> $data
                     * @var array<string> $oldAccountIds
                     */
                    $oldAccountIds = $records->pluck('account_id')->unique();
                    $records->each->update(['account_id' => $data['account_id']]);

                    // update balance for the new account
                    Account::updateAccountBalance($data['account_id']);

                    // update balance for old accounts
                    foreach ($oldAccountIds as $oldAccountId) {
                        Account::updateAccountBalance($oldAccountId);
                    }
                })
                ->deselectRecordsAfterCompletion(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
        ];
    }
}
