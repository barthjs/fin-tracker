<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'tabler-credit-card';

    public static function getSlug(): string
    {
        return __('transaction.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('transaction.navigation_label');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(self::formParts());
    }

    public static function formParts($account = null, $category = null): array
    {
        // account and category for default values in relation manager
        return [
            Section::make()
                ->schema([
                    DateTimePicker::make('date_time')
                        ->label(__('transaction.columns.date'))
                        ->autofocus()
                        ->default(today())
                        ->required(),
                    Select::make('account_id')
                        ->label(__('transaction.columns.account'))
                        ->relationship('account', 'name')
                        ->placeholder(__('transaction.form.account_placeholder'))
                        ->validationMessages(['required' => __('transaction.form.account_validation_message')])
                        ->preload()
                        ->default(fn(): string => $account->id ?? "")
                        ->live(true)
                        ->required()
                        ->searchable()
                        ->createOptionForm(AccountResource::formParts())
                        ->createOptionModalHeading(__('account.buttons.create_heading')),
                    TextInput::make('amount')
                        ->label(__('transaction.columns.amount'))
                        ->suffix(fn($get) => Account::whereId($get('account_id'))->first()->currency->name ?? "")
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
                        ->placeholder(__('transaction.form.category_placeholder'))
                        ->validationMessages(['required' => __('transaction.form.category_validation_message')])
                        ->preload()
                        ->default(fn(): string => $category->id ?? "")
                        ->live(true)
                        ->hint(fn($get): string => __('category.types')[Category::whereId($get('category_id'))->first()->type->name ?? ""] ?? "")
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
                TextColumn::make('date_time')
                    ->label(__('transaction.columns.date'))
                    ->dateTime('Y-m-d, H:i')
                    ->fontFamily('mono')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label(__('transaction.columns.amount'))
                    ->fontFamily('mono')
                    ->numeric(2)
                    ->badge()
                    ->color(fn($record): string => match ($record->category->type->name) {
                        'expense' => 'danger',
                        'revenue' => 'success',
                        default => 'warning',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('destination')
                    ->label(__('transaction.columns.destination'))
                    ->searchable()
                    ->toggleable()
                    ->wrap(),
                ImageColumn::make('account.logo')
                    ->label('')
                    ->circular()
                    ->alignEnd(),
                TextColumn::make('account.name')
                    ->label(__('transaction.columns.account'))
                    ->hiddenOn(AccountResource\RelationManagers\TransactionRelationManager::class)
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('category.name')
                    ->label(__('transaction.columns.category'))
                    ->hiddenOn(CategoryResource\RelationManagers\TransactionRelationManager::class)
                    ->badge()
                    ->color(fn($record): string => match ($record->category->type->name) {
                        'expense' => 'danger',
                        'revenue' => 'success',
                        default => 'warning',
                    })
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.group.name')
                    ->label(__('transaction.columns.group'))
                    ->hiddenOn([CategoryResource\RelationManagers\TransactionRelationManager::class, ListTransactions::class])
                    ->formatStateUsing(fn($state): string => __('category.groups')[$state])
                    ->wrap()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('notes')
                    ->label(__('transaction.columns.notes'))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->paginated(fn(): bool => Transaction::count() > 20)
            ->deferLoading()
            ->extremePaginationLinks()
            ->defaultSort('date_time', 'desc')
            ->persistSortInSession()
            ->striped()
            ->filters([
                SelectFilter::make('account')
                    ->label(__('transaction.columns.account'))
                    ->hiddenOn(AccountResource\RelationManagers\TransactionRelationManager::class)
                    ->relationship('account', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                SelectFilter::make('category')
                    ->label(__('transaction.columns.category'))
                    ->hiddenOn(CategoryResource\RelationManagers\TransactionRelationManager::class)
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                Filter::make('date')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('table.filter.created_from'))
                            ->default(Carbon::today()->startOfYear()),
                        DatePicker::make('created_until')
                            ->label(__('table.filter.created_until'))
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_time', '>=', $date))
                            ->when($data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_time', '<=', $date));
                    })
            ], FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                CreateAction::make('header-create')
                    ->icon('tabler-plus')
                    ->label(__('transaction.buttons.create_button_label'))
                    ->hidden(function ($livewire) {
                        return $livewire instanceof ListTransactions;
                    })
                    ->modalHeading(__('transaction.buttons.create_heading')),
            ])
            ->persistFiltersInSession()
            ->filtersFormColumns(function ($livewire) {
                return $livewire instanceof ListTransactions ? 3 : 2;
            })
            ->actions(self::getActions())
            ->bulkActions(self::getBulkActions())
            ->emptyStateHeading(__('transaction.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('transaction.buttons.create_button_label'))
                    ->modalHeading(__('transaction.buttons.create_heading'))
            ]);
    }

    public static function getActions(): array
    {
        return [
            EditAction::make()
                ->iconButton()
                ->icon('tabler-edit')
                ->modalHeading(__('transaction.buttons.edit_heading'))
                ->using(function (Model $record, array $data): Model {
                    $oldDate = $record->getOriginal('date_time');
                    $oldAmount = $record->getOriginal('amount');
                    $oldAccountId = $record->getOriginal('account_id');
                    $oldCategoryId = $record->getOriginal('category_id');

                    DB::transaction(function () use ($record, $data, $oldDate, $oldAmount, $oldAccountId, $oldCategoryId) {
                        $record->update($data);

                        $newAmount = $record->amount;
                        $newDate = $record->date_time;
                        $newAccountId = $record->account_id;
                        $newCategoryId = $record->category_id;

                        if ($oldAccountId !== $newAccountId || $oldAmount !== $newAmount) {
                            self::updateAccountBalance($oldAccountId);
                            self::updateAccountBalance($newAccountId);
                        }

                        if ($oldCategoryId !== $newCategoryId || $oldDate !== $newDate || $oldAmount !== $newAmount) {
                            Transaction::updateCategoryStatistics($oldCategoryId, $oldDate);
                            Transaction::updateCategoryStatistics($newCategoryId, $newDate);
                        }
                    });

                    return $record;
                }),
            DeleteAction::make()
                ->iconButton()
                ->icon('tabler-trash')
                ->modalHeading(__('transaction.buttons.delete_heading'))
                ->after(function (Transaction $record): Transaction {
                    Transaction::updateCategoryStatistics($record->category_id, $record->date_time);
                    self::updateAccountBalance($record->account_id);
                    return $record;
                })
        ];
    }

    /**
     * @param int $accountId
     * @return void
     */
    private static function updateAccountBalance(int $accountId): void
    {
        $newBalance = Transaction::whereAccountId($accountId)->sum('amount');
        Account::whereId($accountId)->update(['balance' => $newBalance]);
    }

    public static function getBulkActions(): BulkActionGroup
    {
        return BulkActionGroup::make([
            DeleteBulkAction::make()
                ->modalHeading(__('transaction.buttons.bulk_delete_heading'))
                ->after(function (Collection $records) {
                    foreach ($records as $record) {
                        self::updateAccountBalance($record->account_id);
                        Transaction::updateCategoryStatistics($record->category_id, $record->date_time);
                    }
                }),
            BulkAction::make('account')
                ->icon('tabler-edit')
                ->label(__('transaction.buttons.bulk_account'))
                ->form([
                    Select::make('account_id')
                        ->label(__('transaction.columns.account'))
                        ->placeholder(__('transaction.form.account_placeholder'))
                        ->relationship('account', 'name')
                        ->preload()
                        ->required()
                        ->searchable()
                ])
                ->action(function (Collection $records, array $data): void {
                    // save old values before updating
                    $oldAccountIds = $records->pluck('account_id')->unique();
                    $records->each->update(['account_id' => $data['account_id']]);

                    // update balance for new account
                    $newSum = Transaction::whereAccountId($data['account_id'])->sum('amount');
                    Account::whereId($data['account_id'])->update(['balance' => $newSum]);

                    // update balance for old accounts
                    foreach ($oldAccountIds as $oldAccountId) {
                        $oldSum = Transaction::whereAccountId($oldAccountId)->sum('amount');
                        Account::whereId($oldAccountId)->update(['balance' => $oldSum]);
                    }
                })
                ->deselectRecordsAfterCompletion()
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
