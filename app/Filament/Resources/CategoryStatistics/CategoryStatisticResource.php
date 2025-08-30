<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryStatistics;

use App\Enums\Currency;
use App\Enums\TransactionType;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\CategoryStatistics\Pages\ListCategoryStatistics;
use App\Filament\Resources\CategoryStatistics\Widgets\CategoryStatisticChart;
use App\Models\CategoryStatistic;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

final class CategoryStatisticResource extends Resource
{
    protected static ?string $model = CategoryStatistic::class;

    protected static ?int $navigationSort = 1;

    protected static string|BackedEnum|null $navigationIcon = 'tabler-calendar-stats';

    public static function getModelLabel(): string
    {
        return __('category_statistic.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('category_statistic.plural_label');
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return __('category_statistic.slug');
    }

    public static function getWidgets(): array
    {
        return [
            CategoryStatisticChart::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                $query->whereHas('category', function (Builder $query): void {
                    $query->where('type', '!=', TransactionType::Transfer)->where('is_active', true);
                });

                if (! $table->getActiveFiltersCount()) {
                    return $query->where('year', Carbon::now()->year);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('category.label'))
                    ->wrap(),
                TextColumn::make('jan')
                    ->label(__('category_statistic.columns.jan'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('feb')
                    ->label(__('category_statistic.columns.feb'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('mar')
                    ->label(__('category_statistic.columns.mar'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('apr')
                    ->label(__('category_statistic.columns.apr'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('may')
                    ->label(__('category_statistic.columns.may'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('jun')
                    ->label(__('category_statistic.columns.jun'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('jul')
                    ->label(__('category_statistic.columns.jul'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('aug')
                    ->label(__('category_statistic.columns.aug'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('sep')
                    ->label(__('category_statistic.columns.sep'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('oct')
                    ->label(__('category_statistic.columns.oct'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('nov')
                    ->label(__('category_statistic.columns.nov'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
                TextColumn::make('dec')
                    ->label(__('category_statistic.columns.dec'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Currency::getCurrency()))
                    ->toggleable(),
            ])
            ->deferLoading()
            ->paginated(false)
            ->searchable(false)
            ->persistSortInSession()
            ->reorderableColumns()
            ->deferColumnManager(false)
            ->defaultGroup('category.group')
            ->groupingSettingsHidden()
            ->groups([
                Group::make('category.group')
                    ->label('')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (CategoryStatistic $record): string => $record->category->group->getLabel()),
            ])
            ->striped()
            ->filters([
                SelectFilter::make('year')
                    ->label(__('table.filter.year'))
                    ->options(function () {
                        $years = CategoryStatistic::select('year')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                            ->toArray();

                        if (empty($years)) {
                            return [Carbon::now()->year => Carbon::now()->year];
                        }

                        return $years;
                    })
                    ->placeholder(__('table.filter.year'))
                    ->selectablePlaceholder(false),
            ], FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(1)
            ->persistFiltersInSession()
            ->recordUrl(fn (CategoryStatistic $record): string => ViewCategory::getUrl(['record' => $record->category_id]))
            ->emptyStateHeading(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoryStatistics::route('/'),
        ];
    }
}
