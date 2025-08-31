<?php

declare(strict_types=1);

namespace App\Filament\Resources\Portfolios;

use App\Enums\Currency;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Concerns\HasResourceFormFields;
use App\Filament\Concerns\HasResourceInfolistEntries;
use App\Filament\Concerns\HasResourceTableColumns;
use App\Filament\Resources\Portfolios\Pages\ListPortfolios;
use App\Filament\Resources\Portfolios\Pages\ViewPortfolio;
use App\Filament\Resources\Portfolios\RelationManagers\SecuritiesRelationManager;
use App\Filament\Resources\Portfolios\RelationManagers\TradesRelationManager;
use App\Filament\Resources\Users\RelationManagers\PortfoliosRelationManager;
use App\Models\Portfolio;
use BackedEnum;
use Filament\Forms\Components\Field;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class PortfolioResource extends Resource
{
    use HasResourceActions, HasResourceFormFields, HasResourceInfolistEntries, HasResourceTableColumns;

    protected static ?string $model = Portfolio::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-wallet';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('portfolio.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('portfolio.plural_label');
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
            self::nameField(),
            self::currencyField(),
            self::colorField(),
            self::statusToggleField(),
            self::logoField(directory: 'portfolios'),
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
                        self::totalValueEntry('market_value')
                            ->label(__('fields.market_value'))
                            ->color(fn (Portfolio $record): string => $record->marketValueColor)
                            ->money(fn (Portfolio $record): string => $record->currency->value),

                        self::descriptionEntry()
                            ->hidden(fn (Portfolio $record): bool => $record->description === null),
                    ])
                    ->columns([
                        'default' => 2,
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(null)
            ->modelLabel(__('portfolio.label'))
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                if (! $table->getActiveFiltersCount()) {
                    return $query->where('is_active', true);
                }

                return $query;
            })
            ->columns(self::getTableColumns())
            ->paginated(fn (): bool => Portfolio::count() > 20)
            ->filters([
                self::inactiveFilter(),
            ])
            ->emptyStateHeading(__('No :model found', ['model' => self::getPluralModelLabel()]));
    }

    /**
     * @return array<int, Column>
     */
    public static function getTableColumns(): array
    {
        $hidden = PortfoliosRelationManager::class;

        return [
            self::logoAndNameColumn()
                ->state(fn (Portfolio $record): array => [
                    'logo' => $record->logo,
                    'name' => $record->name,
                ]),

            TextColumn::make('market_value')
                ->hiddenOn($hidden)
                ->label(__('fields.market_value'))
                ->badge()
                ->color(fn (Portfolio $record): string => $record->marketValueColor)
                ->money(fn (Portfolio $record): string => $record->currency->value)
                ->summarize(Sum::make()->money(Currency::getCurrency())),

            self::descriptionColumn()
                ->hiddenOn($hidden),

            self::currencyColumn(),
            self::statusColumn(),
        ];
    }

    public static function getRelations(): array
    {
        return [
            SecuritiesRelationManager::class,
            TradesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPortfolios::route('/'),
            'view' => ViewPortfolio::route('/{record}'),
        ];
    }
}
