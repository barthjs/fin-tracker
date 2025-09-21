<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities;

use App\Enums\SecurityType;
use App\Enums\TradeType;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceInfolistEntries;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Securities\Pages\ListSecurities;
use App\Filament\Resources\Securities\Pages\ViewSecurity;
use App\Filament\Resources\Securities\RelationManagers\TradesRelationManager;
use App\Filament\Resources\Users\RelationManagers\SecuritiesRelationManager;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use BackedEnum;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Number;

final class SecurityResource extends Resource
{
    use HasResourceActions, HasResourceFormFields, HasResourceInfolistEntries, HasResourceTableColumns;

    protected static ?string $model = Security::class;

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-file-percent';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('security.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('security.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(self::getFormFields());
    }

    /**
     * @return array<int, Field>
     */
    public static function getFormFields(): array
    {
        return [
            self::nameField()
                ->columnSpanFull(),

            TextInput::make('isin')
                ->label(__('security.fields.isin'))
                ->maxLength(255),

            TextInput::make('symbol')
                ->label(__('security.fields.symbol'))
                ->maxLength(255),

            TextInput::make('price')
                ->label(__('fields.price'))
                ->required()
                ->numeric(),

            Select::make('type')
                ->label(__('fields.type'))
                ->options(SecurityType::class)
                ->default(SecurityType::Stock)
                ->selectablePlaceholder(false)
                ->required(),

            self::colorField(),
            self::statusToggleField(),
            self::logoField(),
            self::descriptionField(),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        self::numericEntry('market_value')
                            ->label(__('fields.market_value'))
                            ->numeric(6),

                        self::numericEntry('price')
                            ->label(__('fields.price'))
                            ->numeric(6),

                        self::numericEntry('total_quantity')
                            ->label(__('security.fields.total_quantity'))
                            ->numeric(6),

                        TextEntry::make('type')
                            ->label(__('fields.type'))
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),
                    ])
                    ->columns([
                        'default' => 2,
                        'md' => 4,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->modelLabel(__('security.label'))
            ->pluralModelLabel(__('security.plural_label'))
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                if (! $table->getActiveFiltersCount()) {
                    return $query->where('is_active', true);
                }

                return $query;
            })
            ->columns(self::getTableColumns())
            ->defaultGroup('type')
            ->groupingSettingsHidden()
            ->groups([
                Group::make('type')
                    ->label('')
                    ->getTitleFromRecordUsing(fn (Security $record): string => $record->type->getLabel())
                    ->collapsible()
                    ->orderQueryUsing(function (Builder $query, string $direction): Builder {
                        $cases = [];
                        foreach (SecurityType::cases() as $case) {
                            $label = str_replace("'", "''", $case->getLabel());
                            $cases[] = "WHEN '".$case->value."' THEN '".mb_strtolower($label)."'";
                        }
                        $caseSql = 'CASE type '.implode(' ', $cases).' END';

                        return $query->orderByRaw($caseSql.' '.($direction === 'desc' ? 'desc' : 'asc'));
                    }),
            ])
            ->filters([
                self::inactiveFilter(),
            ])
            ->recordActions([
                self::tableEditAction()
                    ->using(function (Security $record, array $data): Security {
                        $oldPrice = $record->price;
                        /** @var array<string, mixed> $data * */
                        $record->update($data);

                        if ($data['price'] !== $oldPrice) {
                            /** @var array<string> $portfolios */
                            $portfolios = Trade::where('security_id', $record->id)
                                ->distinct(['portfolio_id'])
                                ->pluck('portfolio_id')
                                ->toArray();

                            if (! empty($portfolios)) {
                                foreach ($portfolios as $portfolio) {
                                    Portfolio::updatePortfolioMarketValue($portfolio);
                                }
                            }
                        }

                        return $record;
                    }),

                self::tableDeleteAction(),
            ]);
    }

    /**
     * @return array<int, Column>
     */
    public static function getTableColumns(?Portfolio $portfolio = null): array
    {
        $hidden = SecuritiesRelationManager::class;

        return [
            self::logoAndNameColumn()
                ->state(fn (Security $record): array => [
                    'logo' => $record->logo,
                    'name' => $record->name,
                ]),

            TextColumn::make('market_value')
                ->label(__('fields.market_value'))
                ->numeric(2)
                ->sortable(),

            TextColumn::make('total_quantity')
                ->hiddenOn($hidden)
                ->label(__('security.fields.total_quantity'))
                ->formatStateUsing(function (Security $record, float $state) use ($portfolio): ?string {
                    // Show only the quantity of the current portfolio on the relation manager
                    if ($portfolio) {
                        $buys = (float) Trade::where('portfolio_id', $portfolio->id)
                            ->where('security_id', $record->id)
                            ->where('type', TradeType::Buy)
                            ->sum('quantity');

                        $sells = (float) Trade::where('portfolio_id', $portfolio->id)
                            ->where('security_id', $record->id)
                            ->where('type', TradeType::Sell)
                            ->sum('quantity');

                        $quantity = $buys - $sells;

                        return Number::format($quantity, 2) ?: null;
                    }

                    return Number::format($state, 2) ?: null;
                })
                ->sortable(),

            TextColumn::make('price')
                ->label(__('fields.price'))
                ->badge()
                ->numeric(2)
                ->sortable(),

            TextColumn::make('isin')
                ->label(__('security.fields.isin'))
                ->searchable()
                ->sortable()
                ->toggleable(),

            TextColumn::make('symbol')
                ->label(__('security.fields.symbol'))
                ->searchable()
                ->sortable()
                ->toggleable(),

            self::descriptionColumn()
                ->hiddenOn($hidden),

            self::statusColumn(),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TradesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecurities::route('/'),
            'view' => ViewSecurity::route('/{record}'),
        ];
    }
}
