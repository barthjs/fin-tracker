<?php

declare(strict_types=1);

namespace App\Filament\Resources\Trades;

use App\Enums\TradeType;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Accounts;
use App\Filament\Resources\Portfolios;
use App\Filament\Resources\Securities;
use App\Filament\Resources\Trades\Pages\ListTrades;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use App\Services\TradeService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class TradeResource extends Resource
{
    use HasResourceActions, HasResourceFormFields, HasResourceTableColumns;

    protected static ?string $model = Trade::class;

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-exchange';

    public static function getModelLabel(): string
    {
        return __('trade.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('trade.plural_label');
    }

    public static function form(Schema $schema, Account|Model|null $account = null, Portfolio|Model|null $portfolio = null, Security|Model|null $security = null): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    self::dateTimePickerField(),

                    self::securitySelectField()
                        ->default(fn (): ?string => $security instanceof Security ? $security->id : null),

                    self::tradeAmountField('quantity')
                        ->label(__('trade.fields.quantity'))
                        ->afterStateUpdated(function (string|float|null $state, Get $get, Set $set): void {
                            $set('total_amount', self::calculateTotalAmount(
                                self::asFloat($state),
                                self::asFloat($get('price')),
                                self::asFloat($get('tax')),
                                self::asFloat($get('fee')),
                                self::asTradeType($get('type')),
                            ));
                        })
                        ->required(),

                    self::tradeAmountField('price')
                        ->label(__('fields.price'))
                        ->afterStateUpdated(function (string|float|null $state, Get $get, Set $set): void {
                            $set('total_amount', self::calculateTotalAmount(
                                self::asFloat($get('quantity')),
                                self::asFloat($state),
                                self::asFloat($get('tax')),
                                self::asFloat($get('fee')),
                                self::asTradeType($get('type')),
                            ));
                        })
                        ->required(),

                    self::tradeAmountField('tax')
                        ->label(__('trade.fields.tax'))
                        ->afterStateUpdated(function (string|float|null $state, Get $get, Set $set): void {
                            $set('total_amount', self::calculateTotalAmount(
                                self::asFloat($get('quantity')),
                                self::asFloat($get('price')),
                                self::asFloat($state),
                                self::asFloat($get('fee')),
                                self::asTradeType($get('type')),
                            ));
                        })
                        ->required(),

                    self::tradeAmountField('fee')
                        ->label(__('trade.fields.fee'))
                        ->afterStateUpdated(function (string|float|null $state, Get $get, Set $set): void {
                            $set('total_amount', self::calculateTotalAmount(
                                self::asFloat($get('quantity')),
                                self::asFloat($get('price')),
                                self::asFloat($get('tax')),
                                self::asFloat($state),
                                self::asTradeType($get('type')),
                            ));
                        })
                        ->required(),

                    self::typeSelectField()
                        ->options(TradeType::class)
                        ->default(TradeType::Buy)
                        ->afterStateUpdated(function (TradeType $state, Get $get, Set $set): void {
                            $set('total_amount', self::calculateTotalAmount(
                                self::asFloat($get('quantity')),
                                self::asFloat($get('price')),
                                self::asFloat($get('tax')),
                                self::asFloat($get('fee')),
                                $state,
                            ));
                        }),

                    TextInput::make('total_amount')
                        ->label(__('trade.fields.total_amount'))
                        ->numeric()
                        ->default(0)
                        ->suffix(fn (Get $get): ?string => Account::find($get('account_id'))?->currency?->value)
                        ->dehydrated(false)
                        ->disabled(),
                ]),

            Section::make()
                ->columns(2)
                ->schema([
                    self::accountSelectField()
                        ->getOptionLabelUsing(fn (?string $value): ?string => Account::find($value)?->name)
                        ->default(fn (): ?string => $account instanceof Account ? $account->id : null)
                        ->live(true),

                    self::portfolioSelectField()
                        ->getOptionLabelUsing(fn (?string $value): ?string => Portfolio::find($value)?->name)
                        ->default(fn (): ?string => $portfolio instanceof Portfolio ? $portfolio->id : null),

                    self::notesField(),
                ]),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->modelLabel(__('trade.label'))
            ->pluralModelLabel(__('trade.plural_label'))
            ->columns([
                self::dateTimeColumn('date_time'),

                TextColumn::make('total_amount')
                    ->label(__('trade.fields.total_amount'))
                    ->alignEnd()
                    ->fontFamily('mono')
                    ->copyable()
                    ->badge()
                    ->color(fn (Trade $record): string => $record->type->getColor())
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('quantity')
                    ->label(__('trade.fields.quantity'))
                    ->alignEnd()
                    ->fontFamily('mono')
                    ->copyable()
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label(__('fields.price'))
                    ->alignEnd()
                    ->fontFamily('mono')
                    ->copyable()
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tax')
                    ->label(__('trade.fields.tax'))
                    ->alignEnd()
                    ->fontFamily('mono')
                    ->copyable()
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('fee')
                    ->label(__('trade.fields.fee'))
                    ->alignEnd()
                    ->fontFamily('mono')
                    ->copyable()
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(),

                self::logoAndNameColumn('account.name')
                    ->hiddenOn(Accounts\RelationManagers\TradesRelationManager::class)
                    ->label(Str::ucfirst(__('account.label')))
                    ->state(fn (Trade $record): array => [
                        'logo' => $record->account->logo,
                        'name' => $record->account->name,
                    ])
                    ->toggleable(),

                self::logoAndNameColumn('portfolio.name')
                    ->hiddenOn(Portfolios\RelationManagers\TradesRelationManager::class)
                    ->label(Str::ucfirst(__('portfolio.label')))
                    ->state(fn (Trade $record): array => [
                        'logo' => $record->portfolio->logo,
                        'name' => $record->portfolio->name,
                    ])
                    ->toggleable(),

                self::logoAndNameColumn('security.name')
                    ->hiddenOn(Securities\RelationManagers\TradesRelationManager::class)
                    ->label(Str::ucfirst(__('security.label')))
                    ->state(fn (Trade $record): array => [
                        'logo' => $record->security->logo,
                        'name' => $record->security->name,
                    ])
                    ->toggleable(),

                self::notesColumn()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date_time', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->hiddenOn(ListTrades::class)
                    ->label(__('fields.type'))
                    ->options(TradeType::class),

                SelectFilter::make('account_id')
                    ->hiddenOn(Accounts\RelationManagers\TradesRelationManager::class)
                    ->label(Str::ucfirst(__('account.label')))
                    ->relationship('account', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('portfolio_id')
                    ->hiddenOn(Portfolios\RelationManagers\TradesRelationManager::class)
                    ->label(Str::ucfirst(__('portfolio.label')))
                    ->relationship('portfolio', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
                    ->multiple()
                    ->preload()
                    ->searchable(),

                SelectFilter::make('security_id')
                    ->hiddenOn(Securities\RelationManagers\TradesRelationManager::class)
                    ->label(Str::ucfirst(__('security.label')))
                    ->relationship('security', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
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
            ->filtersFormColumns(4)
            ->headerActions([
                self::tableCreateAction()
                    ->hidden(fn (mixed $livewire = null): bool => $livewire instanceof ListTrades)
                    /** @phpstan-ignore-next-line */
                    ->action(fn (TradeService $service, array $data): Trade => $service->create($data)),
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
                /** @phpstan-ignore-next-line */
                ->action(fn (TradeService $service, Trade $record, array $data): Trade => $service->update($record, $data)),

            self::tableDeleteAction()
                ->action(fn (TradeService $service, Trade $record) => $service->delete($record)),
        ];
    }

    public static function getBulkActions(): BulkActionGroup
    {
        return BulkActionGroup::make([
            self::tableBulkDeleteAction()
                ->action(fn (TradeService $service, Collection $records) => $service->bulkDelete($records)),

            self::tableBulkEditAction('account_id')
                ->label(__('account.buttons.bulk_edit_account'))
                ->schema([
                    self::accountSelectField(),
                ])
                /** @phpstan-ignore-next-line */
                ->action(fn (TradeService $service, Collection $records, array $data) => $service->bulkUpdate($records, $data))
                ->deselectRecordsAfterCompletion(),

            self::tableBulkEditAction('portfolio_id')
                ->label(__('portfolio.buttons.bulk_edit_portfolio'))
                ->schema([
                    self::portfolioSelectField(),
                ])
                /** @phpstan-ignore-next-line */
                ->action(fn (TradeService $service, Collection $records, array $data) => $service->bulkUpdate($records, $data))
                ->deselectRecordsAfterCompletion(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrades::route('/'),
        ];
    }

    private static function calculateTotalAmount(float $quantity, float $price, float $tax, float $fee, TradeType $type): float
    {
        return match ($type) {
            TradeType::Buy => round($price * $quantity + $tax + $fee, 2),
            TradeType::Sell => round($price * $quantity - ($tax + $fee), 2),
        };
    }

    private static function asFloat(mixed $value): float
    {
        if (is_null($value)) {
            return 0.0;
        }

        if (is_string($value)) {
            $normalized = str_replace([',', ' '], ['', ''], $value);
            if (is_numeric($normalized)) {
                return (float) $normalized;
            }

            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        // Unsupported types (arrays, objects, bool): treat as 0 for safety
        return 0.0;
    }

    private static function asTradeType(mixed $value): TradeType
    {
        if ($value instanceof TradeType) {
            return $value;
        }

        if (is_string($value) || is_int($value)) {
            return TradeType::from($value);
        }

        return TradeType::Buy;
    }
}
