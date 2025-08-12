<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\TransactionGroup;
use App\Filament\Resources\AccountResource\RelationManagers\TransactionRelationManager;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Tables\Columns\LogoColumn;
use BackedEnum;
use Carbon\Carbon;
use Exception;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Panel;
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
use Number;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-credit-card';

    public static function getSlug(?Panel $panel = null): string
    {
        return __('transaction.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('transaction.navigation_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::formParts());
    }

    public static function formParts(?Model $account = null, ?Model $category = null): array
    {
        // account and category for default values in relation manager
        return [
            Section::make()
                ->schema([
                    DateTimePicker::make('date_time')
                        ->label(__('transaction.columns.date'))
                        ->autofocus()
                        ->native(false)
                        ->displayFormat('d.m.Y, H:i')
                        ->seconds(false)
                        ->default(today())
                        ->required(),
                    Select::make('account_id')
                        ->label(__('transaction.columns.account'))
                        ->relationship('account', 'name')
                        ->options(fn (): array => Account::query()
                            ->orderBy('name')
                            ->where('active', true)
                            ->pluck('name', 'id')
                            ->toArray()
                        )
                        ->placeholder(__('transaction.form.account_placeholder'))
                        ->validationMessages(['required' => __('transaction.form.account_validation_message')])
                        ->preload()
                        ->live(true)
                        ->default(fn (): int|string => $account->id ?? '')
                        ->required()
                        ->searchable()
                        ->createOptionForm(AccountResource::formParts())
                        ->createOptionModalHeading(__('account.buttons.create_heading')),
                    TextInput::make('amount')
                        ->label(__('transaction.columns.amount'))
                        ->suffix(fn (Get $get) => Account::whereId($get('account_id'))->first()->currency->name ?? '')
                        ->numeric()
                        ->minValue(-922337203685477580)
                        ->maxValue(9223372036854775807)
                        ->required(),
                ])
                ->columns(3),
            Section::make()
                ->schema([
                    TextInput::make('destination')
                        ->label(__('transaction.columns.destination'))
                        ->datalist(Transaction::query()
                            ->whereBetween('date_time', [today()->subYear()->toDateString(), today()->toDateString()])
                            ->select('destination')
                            ->distinct()
                            ->orderBy('destination')
                            ->pluck('destination')
                            ->toArray())
                        ->maxLength(255)
                        ->required()
                        ->string(),
                    Select::make('category_id')
                        ->label(__('transaction.columns.category'))
                        ->relationship('category', 'name')
                        ->options(fn (): array => Category::query()
                            ->orderBy('name')
                            ->where('active', true)
                            ->pluck('name', 'id')
                            ->toArray()
                        )
                        ->placeholder(__('transaction.form.category_placeholder'))
                        ->validationMessages(['required' => __('transaction.form.category_validation_message')])
                        ->preload()
                        ->live(true)
                        ->default(fn (): int|string => $category->id ?? '')
                        ->hint(fn (Get $get): string => __('category.types')[Category::whereId($get('category_id'))->first()->type->name ?? ''] ?? '')
                        ->required()
                        ->searchable()
                        ->createOptionForm(CategoryResource::formParts())
                        ->createOptionModalHeading(__('category.buttons.create_heading')),
                    Textarea::make('notes')
                        ->label(__('transaction.columns.notes'))
                        ->autosize()
                        ->columnSpanFull()
                        ->maxLength(255)
                        ->rows(1)
                        ->string(),
                ])
                ->columns(2),
        ];
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_time')
                    ->label(__('transaction.columns.date'))
                    ->dateTime('Y-m-d, H:i')
                    ->fontFamily('mono')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label(__('transaction.columns.amount'))
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyableState(fn (float $state) => Number::format($state, 2))
                    ->numeric(2)
                    ->badge()
                    ->color(fn (Transaction $record): string => match ($record->category->type->name) {
                        'expense' => 'danger',
                        'revenue' => 'success',
                        default => 'warning',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('destination')
                    ->label(__('transaction.columns.destination'))
                    ->wrap()
                    ->searchable()
                    ->toggleable(),
                LogoColumn::make('account.name')
                    ->label(__('transaction.columns.account'))
                    ->state(fn (Transaction $record): array => [
                        'logo' => $record->account->logo,
                        'name' => $record->account->name,
                    ])
                    ->hiddenOn(TransactionRelationManager::class)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('category.name')
                    ->label(__('transaction.columns.category'))
                    ->hiddenOn(CategoryResource\RelationManagers\TransactionRelationManager::class)
                    ->badge()
                    ->color(fn (Transaction $record): string => match ($record->category->type->name) {
                        'expense' => 'danger',
                        'revenue' => 'success',
                        default => 'warning',
                    })
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.group')
                    ->label(__('transaction.columns.group'))
                    ->hiddenOn([CategoryResource\RelationManagers\TransactionRelationManager::class, ListTransactions::class])
                    ->formatStateUsing(fn (TransactionGroup $state): string => __('category.groups')[$state->name])
                    ->wrap()
                    ->searchable(true, function (Builder $query, string $search): Builder {
                        $groups = [];
                        foreach (__('category.groups') as $group => $value) {
                            if (mb_stripos($value, $search) !== false) {
                                $groups[] = $group;
                            }
                        }

                        return $query->whereHas('category', fn (Builder $query) => $query->whereIn('group', $groups));
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('notes')
                    ->label(__('transaction.columns.notes'))
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(fn (): bool => Transaction::count() > 20)
            ->deferLoading()
            ->extremePaginationLinks()
            ->defaultSort('date_time', 'desc')
            ->persistSortInSession()
            ->striped()
            ->filters([
                SelectFilter::make('account_id')
                    ->label(__('transaction.columns.account'))
                    ->hiddenOn(TransactionRelationManager::class)
                    ->options(fn (): array => Account::query()
                        ->orderBy('name')
                        ->where('active', true)
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->multiple()
                    ->preload()
                    ->searchable(),
                SelectFilter::make('category_id')
                    ->label(__('transaction.columns.category'))
                    ->hiddenOn(CategoryResource\RelationManagers\TransactionRelationManager::class)
                    ->options(fn (): array => Category::query()
                        ->orderBy('name')
                        ->where('active', true)
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label(__('table.filter.created_from'))
                            ->default(Carbon::today()->startOfYear()),
                        DatePicker::make('created_until')
                            ->label(__('table.filter.created_until')),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'],
                                fn (Builder $query, string $date): Builder => $query->whereDate('date_time', '>=', $date))
                            ->when($data['created_until'],
                                fn (Builder $query, string $date): Builder => $query->whereDate('date_time', '<=', $date));
                    }),
            ], FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                CreateAction::make('header-create')
                    ->icon('tabler-plus')
                    ->label(__('transaction.buttons.create_button_label'))
                    ->hidden(function (mixed $livewire = null) {
                        return $livewire instanceof ListTransactions;
                    })
                    ->modalHeading(__('transaction.buttons.create_heading')),
            ])
            ->persistFiltersInSession()
            ->filtersFormColumns(function (mixed $livewire = null) {
                return $livewire instanceof ListTransactions ? 3 : 2;
            })
            ->recordActions(self::getActions())
            ->toolbarActions(self::getBulkActions())
            ->emptyStateHeading(__('transaction.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('transaction.buttons.create_button_label'))
                    ->modalHeading(__('transaction.buttons.create_heading')),
            ]);
    }

    public static function getActions(): array
    {
        return [
            EditAction::make()
                ->iconButton()
                ->icon('tabler-edit')
                ->modalHeading(__('transaction.buttons.edit_heading'))
                ->using(function (Transaction $record, array $data): Model {
                    $oldDate = $record->getOriginal('date_time');
                    $oldAmount = $record->getOriginal('amount');
                    $oldAccountId = $record->getOriginal('account_id');
                    $oldCategoryId = $record->getOriginal('category_id');

                    DB::transaction(function () use ($record, $data, $oldDate, $oldAmount, $oldAccountId, $oldCategoryId) {
                        $record->update($data);

                        $amount = $record->amount;
                        $date = $record->date_time;
                        $accountId = $record->account_id;
                        $categoryId = $record->category_id;

                        if ($oldAccountId !== $accountId) {
                            Account::updateAccountBalance($oldAccountId);
                            Account::updateAccountBalance($accountId);
                        } elseif ($oldAmount !== $amount) {
                            Account::updateAccountBalance($accountId);
                        }

                        if ($oldCategoryId !== $categoryId || $date->notEqualTo($oldDate) || $oldAmount !== $amount) {
                            Transaction::updateCategoryStatistics($oldCategoryId, $oldDate);
                            Transaction::updateCategoryStatistics($categoryId, $date);
                        }
                    });

                    return $record;
                }),
            DeleteAction::make()
                ->iconButton()
                ->icon('tabler-trash')
                ->modalHeading(__('transaction.buttons.delete_heading'))
                ->after(function (Transaction $record): Transaction {
                    Account::updateAccountBalance($record->account_id);
                    Transaction::updateCategoryStatistics($record->category_id, $record->date_time);

                    return $record;
                }),
        ];
    }

    public static function getBulkActions(): BulkActionGroup
    {
        return BulkActionGroup::make([
            DeleteBulkAction::make()
                ->modalHeading(__('transaction.buttons.bulk_delete_heading'))
                ->after(function (Collection $records) {
                    $deletedAccountIds = [];

                    foreach ($records as $record) {
                        if (! in_array($record->account_id, $deletedAccountIds)) {
                            $deletedAccountIds[] = $record->account_id;
                        }
                    }
                    foreach ($deletedAccountIds as $accountId) {
                        Account::updateAccountBalance($accountId);
                    }

                    foreach ($records as $record) {
                        Transaction::updateCategoryStatistics($record->category_id, $record->date_time);
                    }
                }),
            BulkAction::make('account')
                ->icon('tabler-edit')
                ->label(__('transaction.buttons.bulk_account'))
                ->form([
                    Select::make('account_id')
                        ->label(__('transaction.columns.account'))
                        ->relationship('account', 'name')
                        ->placeholder(__('transaction.form.account_placeholder'))
                        ->validationMessages(['required' => __('transaction.form.account_validation_message')])
                        ->preload()
                        ->required()
                        ->searchable(),
                ])
                ->action(function (Collection $records, array $data): void {
                    // save old values before updating
                    $oldAccountIds = $records->pluck('account_id')->unique();
                    $records->each->update(['account_id' => $data['account_id']]);

                    // update balance for new account
                    Account::updateAccountBalance((int) ($data['account_id']));

                    // update balance for old accounts
                    foreach ($oldAccountIds as $oldAccountId) {
                        Account::updateAccountBalance($oldAccountId);
                    }
                })
                ->deselectRecordsAfterCompletion(),
            /*
            BulkAction::make('category')
                ->icon('tabler-edit')
                ->label(__('transaction.buttons.bulk_category'))
                ->form([
                    Select::make('category_id')
                        ->label(__('transaction.columns.category'))
                        ->relationship('category', 'name')
                        ->placeholder(__('transaction.form.category_placeholder'))
                        ->preload()
                        ->required()
                        ->searchable()
                ])
                // Todo this doesnt work all the time, some values don't get updated, disabled until fixed
                ->action(function (Collection $records, array $data): void {
                    // save old values before updating
                    $oldCategories = $records->pluck('category_id', 'date_time');
                    $records->each->update(['category_id' => $data['category_id']]);

                    // update new values
                    $dates = Transaction::whereCategoryId($data['category_id'])
                        ->pluck('date_time')
                        ->map(fn($date) => Carbon::parse($date)->format('Y-m'))
                        ->unique();
                    foreach ($dates as $date) {
                        Transaction::updateCategoryStatistics($data['category_id'], $date);
                    }

                    // update old values
                    $groupedData = [];
                    foreach ($oldCategories as $datetime => $categoryId) {
                        $monthKey = Carbon::parse($datetime)->format('Y-m');

                        $groupedData[$monthKey][] = $categoryId;
                    }

                    foreach ($groupedData as $month => $categories) {
                        foreach (array_unique($categories) as $categoryId) {
                            Transaction::updateCategoryStatistics($categoryId, $month);
                        }
                    }
                })
                ->deselectRecordsAfterCompletion(),
            */
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
        ];
    }
}
