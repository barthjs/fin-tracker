<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities;

use App\Enums\Country;
use App\Enums\SecurityRegion;
use App\Enums\SecuritySector;
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
use App\Services\SecurityService;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Number;

final class SecurityResource extends Resource
{
    use HasResourceActions;
    use HasResourceFormFields;
    use HasResourceInfolistEntries;
    use HasResourceTableColumns;

    protected static ?string $model = Security::class;

    protected static ?int $navigationSort = 8;

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
     * Returns the form sections of a security, shared by the resource form and inline creation.
     *
     * @return array<int, Section>
     */
    public static function getFormFields(): array
    {
        return [
            Section::make(__('security.sections.general'))
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    self::nameField()
                        ->columnSpanFull(),

                    Select::make('type')
                        ->label(__('fields.type'))
                        ->options(SecurityType::options())
                        ->default(SecurityType::Stock)
                        ->selectablePlaceholder(false)
                        ->required(),

                    TextInput::make('price')
                        ->label(__('fields.price'))
                        ->required()
                        ->numeric(),

                    TextInput::make('isin')
                        ->label(__('security.fields.isin'))
                        ->maxLength(12),

                    TextInput::make('symbol')
                        ->label(__('security.fields.symbol'))
                        ->maxLength(10),

                    TextInput::make('exchange')
                        ->label(__('security.fields.exchange'))
                        ->maxLength(50),

                    TextInput::make('ter')
                        ->label(__('security.fields.ter'))
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(99.999)
                        ->step(0.001),
                ]),

            Section::make(__('security.sections.classification'))
                ->columnSpanFull()
                ->columns(3)
                ->collapsible()
                ->schema([
                    self::regionField(),
                    self::sectorField(),
                    self::countryField(),
                ]),

            Section::make(__('security.sections.appearance'))
                ->columnSpanFull()
                ->columns(2)
                ->collapsible()
                ->schema([
                    self::logoField('securities'),
                    self::colorField(),

                    TextInput::make('website')
                        ->label(__('security.fields.website'))
                        ->url()
                        ->maxLength(255),

                    self::statusToggleField(),

                    self::descriptionField()
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * Returns the select field for the region of a security.
     */
    public static function regionField(): Select
    {
        return Select::make('region')
            ->label(__('security.fields.region'))
            ->options(SecurityRegion::options())
            ->placeholder(__('security.unassigned'));
    }

    /**
     * Returns the select field for the sector of a security.
     */
    public static function sectorField(): Select
    {
        return Select::make('sector')
            ->label(__('security.fields.sector'))
            ->options(SecuritySector::options())
            ->placeholder(__('security.unassigned'));
    }

    /**
     * Returns the select field for the country of a security.
     */
    public static function countryField(): Select
    {
        return Select::make('country')
            ->label(__('security.fields.country'))
            ->options(Country::options())
            ->searchable();
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

                        self::numericEntry('ter')
                            ->label(__('security.fields.ter'))
                            ->formatStateUsing(fn (float $state): string => Number::percentage($state, 2, 3, auth()->user()->locale) ?: '')
                            ->placeholder('-'),
                    ])
                    ->columns([
                        'default' => 2,
                        'md' => 4,
                    ]),

                Section::make(__('security.sections.classification'))
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('type')
                            ->label(__('fields.type'))
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold),

                        TextEntry::make('region')
                            ->label(__('security.fields.region'))
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->placeholder(__('security.unassigned')),

                        TextEntry::make('sector')
                            ->label(__('security.fields.sector'))
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->placeholder(__('security.unassigned')),

                        TextEntry::make('country')
                            ->label(__('security.fields.country'))
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->placeholder('-'),

                        TextEntry::make('isin')
                            ->label(__('security.fields.isin'))
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->placeholder('-'),

                        TextEntry::make('symbol')
                            ->label(__('security.fields.symbol'))
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->placeholder('-'),

                        TextEntry::make('exchange')
                            ->label(__('security.fields.exchange'))
                            ->size(TextSize::Medium)
                            ->weight(FontWeight::SemiBold)
                            ->placeholder('-'),

                        TextEntry::make('website')
                            ->label(__('security.fields.website'))
                            ->url(fn (Security $record): ?string => $record->website)
                            ->openUrlInNewTab()
                            ->placeholder('-'),
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
                // The inactive filter replaces the default of showing active securities only
                if ($table->getFilter('inactive')?->getState()['isActive'] ?? false) {
                    return $query;
                }

                return $query->where('is_active', true);
            })
            ->columns(self::getTableColumns())
            ->defaultGroup('type')
            ->groups([
                Group::make('type')
                    ->label(__('fields.type'))
                    ->getTitleFromRecordUsing(fn (Security $record): string => $record->type->getLabel())
                    ->collapsible()
                    ->orderQueryUsing(fn (Builder $query, string $direction): Builder => SecurityType::orderQueryUsing($query, 'type', $direction)),

                Group::make('region')
                    ->label(__('security.fields.region'))
                    ->getTitleFromRecordUsing(fn (Security $record): string => $record->region?->getLabel() ?? __('security.unassigned'))
                    ->collapsible()
                    ->orderQueryUsing(fn (Builder $query, string $direction): Builder => SecurityRegion::orderQueryUsing($query, 'region', $direction)),

                Group::make('sector')
                    ->label(__('security.fields.sector'))
                    ->getTitleFromRecordUsing(fn (Security $record): string => $record->sector?->getLabel() ?? __('security.unassigned'))
                    ->collapsible()
                    ->orderQueryUsing(fn (Builder $query, string $direction): Builder => SecuritySector::orderQueryUsing($query, 'sector', $direction)),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('fields.type'))
                    ->options(SecurityType::options())
                    ->multiple(),

                SelectFilter::make('region')
                    ->label(__('security.fields.region'))
                    ->options(SecurityRegion::options())
                    ->multiple(),

                SelectFilter::make('sector')
                    ->label(__('security.fields.sector'))
                    ->options(SecuritySector::options())
                    ->multiple(),

                SelectFilter::make('country')
                    ->label(__('security.fields.country'))
                    ->options(Country::options())
                    ->multiple()
                    ->searchable(),

                self::inactiveFilter(),
            ])
            ->toolbarActions(self::getBulkActions())
            ->recordActions([
                self::tableEditAction()
                    /** @phpstan-ignore-next-line */
                    ->action(fn (SecurityService $service, Security $record, array $data): Security => $service->update($record, $data)),

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

            self::numericColumn('market_value')
                ->label(__('fields.market_value')),

            self::numericColumn('total_quantity')
                ->hiddenOn($hidden)
                ->label(__('security.fields.total_quantity'))
                ->formatStateUsing(function (Security $record, float $state) use ($portfolio): ?string {
                    // Show only the quantity of the current portfolio on the relation manager
                    if ($portfolio instanceof Portfolio) {
                        $buys = (float) Trade::query()->where('portfolio_id', $portfolio->id)
                            ->where('security_id', $record->id)
                            ->where('type', TradeType::Buy)
                            ->sum('quantity');

                        $sells = (float) Trade::query()->where('portfolio_id', $portfolio->id)
                            ->where('security_id', $record->id)
                            ->where('type', TradeType::Sell)
                            ->sum('quantity');

                        $quantity = $buys - $sells;

                        return Number::format($quantity, 2, locale: auth()->user()->locale) ?: null;
                    }

                    return Number::format($state, 2, locale: auth()->user()->locale) ?: null;
                }),

            self::numericColumn('price')
                ->label(__('fields.price'))
                ->badge(),

            self::nameColumn('isin')
                ->label(__('security.fields.isin')),

            self::nameColumn('symbol')
                ->label(__('security.fields.symbol')),

            TextColumn::make('region')
                ->label(__('security.fields.region'))
                ->placeholder(__('security.unassigned'))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('sector')
                ->label(__('security.fields.sector'))
                ->placeholder(__('security.unassigned'))
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('country')
                ->label(__('security.fields.country'))
                ->placeholder('-')
                ->toggleable(isToggledHiddenByDefault: true),

            self::descriptionColumn()
                ->hiddenOn($hidden),

            self::statusColumn(),
        ];
    }

    /**
     * Returns the bulk actions that set one classification field on all selected securities.
     *
     * @return array<int, BulkActionGroup>
     */
    public static function getBulkActions(): array
    {
        $actions = [];
        foreach ([self::regionField(), self::sectorField(), self::countryField()] as $field) {
            $name = $field->getName();

            $actions[] = BulkAction::make($name)
                ->label(__('security.buttons.bulk_edit_'.$name))
                ->schema([$field])
                ->action(function (Collection $records, array $data) use ($name): void {
                    /** @var Collection<int, Security> $records */
                    $records->each->update([$name => $data[$name]]);
                })
                ->deselectRecordsAfterCompletion();
        }

        return [
            BulkActionGroup::make($actions)
                ->label(__('security.buttons.bulk_edit_classification'))
                ->icon('tabler-edit'),
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
