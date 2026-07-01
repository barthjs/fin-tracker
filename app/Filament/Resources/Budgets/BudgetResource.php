<?php

declare(strict_types=1);

namespace App\Filament\Resources\Budgets;

use App\Actions\CalculateBudgetUsage;
use App\Data\BudgetUsage;
use App\Enums\Currency;
use App\Enums\NotificationEventType;
use App\Enums\PeriodUnit;
use App\Enums\TransactionType;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceInfolistEntries;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Budgets\Pages\ListBudgets;
use App\Filament\Resources\Budgets\Pages\ViewBudget;
use App\Filament\Resources\Budgets\RelationManagers\TransactionsRelationManager;
use App\Models\Budget;
use App\Models\Category;
use App\Services\BudgetService;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

final class BudgetResource extends Resource
{
    use HasResourceActions;
    use HasResourceFormFields;
    use HasResourceInfolistEntries;
    use HasResourceTableColumns;

    protected static ?string $model = Budget::class;

    protected static ?int $navigationSort = 8;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-target';

    public static function getModelLabel(): string
    {
        return __('budget.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('budget.plural_label');
    }

    /**
     * Uses the category name as title, since a category has at most one budget.
     */
    public static function getRecordTitle(?Model $record): string
    {
        if (! $record instanceof Budget) {
            return self::getModelLabel();
        }

        return $record->category->name;
    }

    /**
     * Reports that every budget has a title, since it is taken from the category.
     */
    public static function hasRecordTitle(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::getFormFields());
    }

    /**
     * @return array<int, Grid|Section>
     */
    public static function getFormFields(): array
    {
        return [
            Section::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            self::categorySelectField(),

                            self::amountField()
                                ->minValue(0.01)
                                ->suffix(Currency::getCurrency()),
                        ]),

                    self::statusToggleField(),
                ]),

            Section::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('period_unit')
                                ->label(__('budget.fields.period_unit'))
                                ->options(PeriodUnit::class)
                                ->default(PeriodUnit::Month)
                                ->selectablePlaceholder(false)
                                ->live()
                                ->required(),

                            self::periodFrequencyField(__('budget.fields.period_frequency'))
                                ->required(),
                        ]),

                    Grid::make(2)
                        ->schema([
                            DatePicker::make('starts_at')
                                ->label(__('budget.fields.starts_at'))
                                ->default(today()->startOfMonth()->toDateString())
                                ->required(),

                            DatePicker::make('ends_at')
                                ->label(__('budget.fields.ends_at'))
                                ->nullable()
                                ->minDate(fn (Get $get): mixed => $get('starts_at'))
                                ->afterOrEqual('starts_at')
                                ->validationMessages([
                                    'after_or_equal' => __('validation.after_or_equal', [
                                        'attribute' => __('budget.fields.ends_at'),
                                        'date' => __('budget.fields.starts_at'),
                                    ]),
                                ]),
                        ]),
                ]),

            Section::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('warning_threshold')
                                ->label(__('budget.fields.warning_threshold'))
                                ->helperText(__('budget.fields.warning_threshold_helper'))
                                ->numeric()
                                ->nullable()
                                ->minValue(1)
                                ->maxValue(99)
                                ->suffix('%'),

                            self::notificationToggleField('notify_on_exceed', 'exceed_targets')
                                ->label(__('budget.fields.notify_on_exceed')),
                        ]),

                    self::notificationTargetsField('exceed_targets', NotificationEventType::BUDGET_ALERT, 'notify_on_exceed'),
                ]),
        ];
    }

    public static function categorySelectField(string $column = 'category_id'): Select
    {
        return Select::make($column)
            ->label(Str::ucfirst(__('category.label')))
            ->selectablePlaceholder(false)
            ->relationship(
                'category',
                'name',
                fn (Builder $query): Builder => $query
                    ->where('is_active', true)
                    ->where('type', TransactionType::Expense)
                    ->whereDoesntHave('budget')
            )
            ->getOptionLabelUsing(fn (?string $value): ?string => Category::query()->find($value)?->name)
            ->preload()
            ->searchable()
            ->required()
            ->unique('budgets', 'category_id', ignoreRecord: true)
            ->validationMessages(['unique' => __('budget.validation.category_unique')]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns([
                        'md' => 4,
                        'default' => 2,
                    ])
                    ->schema([
                        TextEntry::make('category.name')
                            ->label(Str::ucfirst(__('category.label'))),

                        self::numericEntry('amount')
                            ->label(__('budget.fields.amount'))
                            ->money(Currency::getCurrency()),

                        TextEntry::make('period_unit')
                            ->label(__('budget.fields.period_frequency'))
                            ->state(fn (Budget $record): string => $record->period_unit->getLabelByFrequency($record->period_frequency)),

                        TextEntry::make('warning_threshold')
                            ->label(__('budget.fields.warning_threshold'))
                            ->state(fn (Budget $record): string => $record->warning_threshold === null
                                ? __('budget.fields.warning_threshold_none')
                                : $record->warning_threshold.' %'),
                    ]),

                Section::make(__('budget.sections.current_period'))
                    ->description(fn (ViewBudget $livewire): string => $livewire->usage()->periodStart->format('d.m.Y')
                        .' – '.$livewire->usage()->lastDay()->format('d.m.Y'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        self::numericEntry('spent')
                            ->label(__('budget.fields.spent'))
                            ->state(fn (ViewBudget $livewire): string => self::formatMoney($livewire->usage()->spent)),

                        self::numericEntry('remaining')
                            ->label(__('budget.fields.remaining'))
                            ->state(fn (ViewBudget $livewire): string => self::formatMoney($livewire->usage()->remaining)),

                        ViewEntry::make('usage')
                            ->hiddenLabel()
                            ->view('filament.infolists.entries.budget-progress-entry')
                            ->state(fn (Budget $record, ViewBudget $livewire): array => self::progressState($record, $livewire->usage()))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->modelLabel(__('budget.label'))
            ->pluralModelLabel(__('budget.plural_label'))
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['category', 'user']))
            ->columns([
                self::nameColumn('category.name')
                    ->label(Str::ucfirst(__('category.label')))
                    ->weight(FontWeight::SemiBold),

                self::amountColumn(),

                TextColumn::make('usage')
                    ->label(__('budget.fields.usage'))
                    ->view('filament.tables.columns.budget-progress-column')
                    ->state(fn (Budget $record): array => self::usageState($record)),

                TextColumn::make('period_unit')
                    ->label(__('budget.fields.period_frequency'))
                    ->state(fn (Budget $record): string => $record->period_unit->getLabelByFrequency($record->period_frequency))
                    ->badge()
                    ->color('gray')
                    ->sortable(['period_frequency', 'period_unit'])
                    ->toggleable(),

                self::statusColumn(),
            ])
            ->defaultSort(fn (Builder $query): Builder => self::orderByCategoryName($query))
            ->filters([
                self::categoryFilter(),

                SelectFilter::make('period_unit')
                    ->label(__('budget.fields.period_frequency'))
                    ->options(PeriodUnit::class),

                self::inactiveFilter(),
            ], FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->recordActions([
                self::tableEditAction()
                    /** @phpstan-ignore-next-line */
                    ->action(fn (BudgetService $service, Budget $record, array $data): Budget => $service->update($record, $data)),

                self::tableDeleteAction(),
            ])
            ->toolbarActions([
                self::tableBulkDeleteAction(),
            ]);
    }

    /**
     * Calculates the usage of a budget and returns the state of its progress bar.
     *
     * @return array{
     *     label: string,
     *     percentage: int,
     *     width: int,
     *     color: string
     * }
     */
    public static function usageState(Budget $record): array
    {
        return self::progressState($record, resolve(CalculateBudgetUsage::class)($record));
    }

    /**
     * Returns label, percentage, bar width and color of the progress bar for an already calculated usage.
     *
     * @return array{
     *     label: string,
     *     percentage: int,
     *     width: int,
     *     color: string
     * }
     */
    public static function progressState(Budget $record, BudgetUsage $usage): array
    {
        $percentage = (int) round($usage->percentage);

        $color = 'primary';
        if ($usage->hasReachedThreshold(100)) {
            $color = 'danger';
        } elseif ($record->warning_threshold !== null && $usage->hasReachedThreshold($record->warning_threshold)) {
            $color = 'warning';
        }

        return [
            'label' => self::formatMoney($usage->spent).' / '.self::formatMoney($usage->budgeted),
            'percentage' => $percentage,
            'width' => min(100, max(0, $percentage)),
            'color' => $color,
        ];
    }

    /**
     * Orders budgets by the name of their category, since budgets have no name of their own.
     *
     * @param  Builder<Budget>  $query
     * @return Builder<Budget>
     */
    public static function orderByCategoryName(Builder $query): Builder
    {
        return $query->orderBy(
            Category::query()->select('name')->whereColumn('categories.id', 'budgets.category_id')
        );
    }

    public static function getRelations(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBudgets::route('/'),
            'view' => ViewBudget::route('/{record}'),
        ];
    }

    public static function configureCreateAction(): CreateAction
    {
        return self::createAction()
            // @phpstan-ignore-next-line
            ->using(fn (BudgetService $service, array $data): Budget => $service->create($data));
    }

    /**
     * Formats an amount in the app currency and the locale of the user.
     */
    private static function formatMoney(float $value): string
    {
        $formatted = Number::currency($value, Currency::getCurrency(), auth()->user()->locale);

        // @codeCoverageIgnoreStart
        if ($formatted === false) {
            return '';
        }

        // @codeCoverageIgnoreEnd

        return $formatted;
    }
}
