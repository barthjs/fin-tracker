<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions;

use App\Enums\NotificationEventType;
use App\Enums\PeriodUnit;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceInfolistEntries;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Accounts;
use App\Filament\Resources\Categories;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use App\Filament\Resources\Subscriptions\RelationManagers\TransactionsRelationManager;
use App\Models\Account;
use App\Models\Category;
use App\Models\NotificationTarget;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class SubscriptionResource extends Resource
{
    use HasResourceActions, HasResourceFormFields, HasResourceInfolistEntries, HasResourceTableColumns;

    protected static ?string $model = Subscription::class;

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-calendar-repeat';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('subscription.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('subscription.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::getFormFields());
    }

    /**
     * Account and category for default values in relation manager.
     *
     * @return array<int, Grid|Section>
     */
    public static function getFormFields(?Account $account = null, ?Category $category = null): array
    {
        return [
            Grid::make(3)
                ->columnSpanFull()
                ->schema([
                    Section::make()
                        ->columnSpan(2)
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    self::nameField(),
                                    self::amountField(),
                                ]),

                            Grid::make(2)
                                ->schema([
                                    self::accountSelectField()
                                        ->default(fn (): ?string => $account instanceof Account ? $account->id : null),

                                    self::categorySelectField()
                                        ->default(fn (): ?string => $category instanceof Category ? $category->id : null),
                                ]),

                            self::descriptionField(),
                        ]),

                    Section::make()
                        ->columnSpan(1)
                        ->schema([
                            self::logoField('subscriptions'),
                            self::colorField(),
                            self::statusToggleField(),
                        ]),
                ]),

            Section::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Select::make('period_unit')
                                ->label(__('subscription.fields.period_unit'))
                                ->options(PeriodUnit::class)
                                ->default(PeriodUnit::Month)
                                ->selectablePlaceholder(false)
                                ->live()
                                ->required(),

                            TextInput::make('period_frequency')
                                ->label(__('subscription.fields.period_frequency'))
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->maxValue(365)
                                ->helperText(function (Get $get): string {
                                    $unit = $get('period_unit');
                                    $frequency = $get('period_frequency');

                                    $periodUnit = null;
                                    if ($unit instanceof PeriodUnit) {
                                        $periodUnit = $unit;
                                    } elseif (is_string($unit)) {
                                        $periodUnit = PeriodUnit::tryFrom($unit);
                                    }

                                    if ($periodUnit && is_numeric($frequency)) {
                                        return $periodUnit->getLabelByFrequency((int) $frequency);
                                    }

                                    return '';
                                }),

                            Toggle::make('auto_generate_transaction')
                                ->label(__('subscription.fields.auto_generate_transaction'))
                                ->helperText(__('subscription.hints.auto_generate'))
                                ->default(true)
                                ->inline(false),
                        ]),

                    Grid::make(3)
                        ->schema([
                            DatePicker::make('started_at')
                                ->label(__('subscription.fields.started_at'))
                                ->live()
                                ->default(today()->toDateString())
                                ->required()
                                ->afterStateUpdated(function (mixed $state, Set $set, Get $get): void {
                                    if (! is_string($state) || blank($state)) {
                                        return;
                                    }

                                    if (blank($get('next_payment_date'))) {
                                        $set('next_payment_date', Carbon::parse($state)->toDateString());
                                    }
                                }),

                            DatePicker::make('next_payment_date')
                                ->label(__('subscription.fields.next_payment_date'))
                                ->helperText(__('subscription.hints.next_payment'))
                                ->live()
                                ->required()
                                ->minDate(fn (Get $get): mixed => $get('started_at'))
                                ->afterOrEqual('started_at')
                                ->validationMessages([
                                    'after_or_equal' => __('validation.after_or_equal', [
                                        'attribute' => __('subscription.fields.next_payment_date'),
                                        'date' => __('subscription.fields.started_at'),
                                    ]),
                                ]),

                            DatePicker::make('ended_at')
                                ->label(__('subscription.fields.ended_at'))
                                ->nullable()
                                ->minDate(fn (Get $get): mixed => $get('next_payment_date'))
                                ->afterOrEqual('next_payment_date')
                                ->validationMessages([
                                    'after_or_equal' => __('validation.after_or_equal', [
                                        'attribute' => __('subscription.fields.ended_at'),
                                        'date' => __('subscription.fields.next_payment_date'),
                                    ]),
                                ]),
                        ]),
                ]),

            Section::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('remind_before_payment')
                                ->label(__('subscription.fields.remind_before_payment'))
                                ->live()
                                ->default(false)
                                ->inline(false),

                            TextInput::make('reminder_days_before')
                                ->label(__('subscription.fields.reminder_days_before'))
                                ->visible(fn (Get $get): bool => (bool) $get('remind_before_payment'))
                                ->numeric()
                                ->default(3)
                                ->minValue(1)
                                ->maxValue(30),
                        ]),

                    CheckboxList::make('reminder_targets')
                        ->label(Str::ucfirst(__('notification_target.plural_label')))
                        ->visible(fn (Get $get): bool => (bool) $get('remind_before_payment'))
                        ->options(fn (): array => NotificationTarget::where('is_active', true)->pluck('name', 'id')->toArray())
                        ->columns(3)
                        ->gridDirection('row')
                        ->bulkToggleable()
                        ->default(fn (): array => NotificationTarget::where('is_active', true)
                            ->where('is_default', true)
                            ->pluck('id')
                            ->toArray()
                        )
                        ->loadStateFromRelationshipsUsing(function (Subscription $record, CheckboxList $component): void {
                            $targetIds = $record->notificationAssignments()
                                ->where('event_type', NotificationEventType::SUBSCRIPTION_REMINDER)
                                ->pluck('notification_target_id')
                                ->toArray();

                            $component->state($targetIds);
                        }),
                ]),
        ];
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
                        self::numericEntry('amount')
                            ->label(__('fields.amount'))
                            ->money(fn (Subscription $record): string => $record->account->currency->value),

                        TextEntry::make('period_unit')
                            ->label(__('subscription.fields.period_frequency'))
                            ->state(fn (Subscription $record): string => $record->period_unit->getLabelByFrequency($record->period_frequency)),

                        self::dateEntry('next_payment_date')
                            ->label(__('subscription.fields.next_payment_date'))
                            ->weight(FontWeight::Bold),

                        self::dateEntry('last_generated_at')
                            ->label(__('subscription.fields.last_generated_at'))
                            ->placeholder(__('subscription.fields.last_generated_at_placeholder')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->modelLabel(__('subscription.label'))
            ->pluralModelLabel(__('subscription.plural_label'))
            ->columns([
                self::logoAndNameColumn()
                    ->state(fn (Subscription $record): array => [
                        'logo' => $record->logo,
                        'name' => $record->name,
                    ]),

                self::amountColumn(),

                TextColumn::make('next_payment_date')
                    ->label(__('subscription.fields.next_payment_date'))
                    ->date('m.d.Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('period_unit')
                    ->label(__('subscription.fields.period_frequency'))
                    ->state(fn (Subscription $record): string => $record->period_unit->getLabelByFrequency($record->period_frequency))
                    ->badge()
                    ->color('gray')
                    ->sortable(['period_frequency', 'period_unit'])
                    ->toggleable(),

                self::logoAndNameColumn('account.name')
                    ->hiddenOn(Accounts\RelationManagers\SubscriptionsRelationManager::class)
                    ->label(Str::ucfirst(__('account.label')))
                    ->state(fn (Subscription $record): array => [
                        'logo' => $record->account->logo,
                        'name' => $record->account->name,
                    ]),

                self::nameColumn('category.name')
                    ->hiddenOn(Categories\RelationManagers\SubscriptionsRelationManager::class)
                    ->label(Str::ucfirst(__('category.label'))),

                self::updatedAtColumn('last_generated_at')
                    ->label(__('subscription.fields.last_generated_at'))
                    ->placeholder(__('subscription.fields.last_generated_at_placeholder'))
                    ->toggleable(),

                self::statusColumn(),
            ])
            ->defaultSort('next_payment_date', 'asc')
            ->filters([
                Filter::make('upcoming_payments')
                    ->schema([
                        DatePicker::make('due_until')
                            ->label(__('subscription.fields.due_until'))
                            ->default(now()->addMonth()->toDateString()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        /** @var array{due_until: string} $data */
                        return $query->when(
                            $data['due_until'],
                            fn (Builder $query, string $date): Builder => $query->whereDate('next_payment_date', '<=', $date),
                        );
                    }),

                self::accountFilter()
                    ->hiddenOn(Accounts\RelationManagers\SubscriptionsRelationManager::class),

                self::categoryFilter()
                    ->hiddenOn(Categories\RelationManagers\SubscriptionsRelationManager::class),

                SelectFilter::make('period_unit')
                    ->label(__('subscription.fields.period_frequency'))
                    ->options(PeriodUnit::class),

                TernaryFilter::make('auto_generate_transaction')
                    ->label(__('subscription.fields.auto_generate_transaction')),

                self::inactiveFilter(),
            ], FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->headerActions([
                self::tableCreateAction()
                    ->hidden(fn (mixed $livewire = null): bool => $livewire instanceof ListSubscriptions)
                    /** @phpstan-ignore-next-line */
                    ->action(fn (SubscriptionService $service, array $data): Subscription => $service->create($data)),
            ])
            ->recordActions([
                self::tableEditAction()
                    /** @phpstan-ignore-next-line */
                    ->action(fn (SubscriptionService $service, Subscription $record, array $data): Subscription => $service->update($record, $data)),

                self::tableDeleteAction(),
            ])
            ->toolbarActions([
                self::tableBulkDeleteAction(),
            ]);
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
            'index' => ListSubscriptions::route('/'),
            'view' => ViewSubscription::route('/{record}'),
        ];
    }
}
