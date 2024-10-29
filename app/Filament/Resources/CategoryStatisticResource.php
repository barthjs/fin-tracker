<?php

namespace App\Filament\Resources;

use App\Enums\TransactionType;
use App\Filament\Resources\CategoryStatisticResource\Pages;
use App\Models\CategoryStatistic;
use Exception;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CategoryStatisticResource extends Resource
{
    protected static ?string $model = CategoryStatistic::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'tabler-calendar-stats';

    public static function getSlug(): string
    {
        return __('category_statistic.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('category_statistic.navigation_label');
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('category', function (Builder $query) {
                    $query->where('type', '!=', TransactionType::transfer)->where('active', '=', true);
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('transaction.columns.category'))
                    ->wrap(),
                Tables\Columns\TextColumn::make('jan')
                    ->label(__('category_statistic.columns.jan'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('feb')
                    ->label(__('category_statistic.columns.feb'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mar')
                    ->label(__('category_statistic.columns.mar'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('apr')
                    ->label(__('category_statistic.columns.apr'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('may')
                    ->label(__('category_statistic.columns.may'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jun')
                    ->label(__('category_statistic.columns.jun'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jul')
                    ->label(__('category_statistic.columns.jul'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('aug')
                    ->label(__('category_statistic.columns.aug'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sep')
                    ->label(__('category_statistic.columns.sep'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('oct')
                    ->label(__('category_statistic.columns.oct'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nov')
                    ->label(__('category_statistic.columns.nov'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('dec')
                    ->label(__('category_statistic.columns.dec'))
                    ->alignEnd()
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label(''))
                    ->toggleable(),
            ])
            ->paginated(false)
            ->searchable(false)
            ->defaultSort('year')
            ->persistSortInSession()
            ->defaultGroup('category.group')
            ->groupingSettingsHidden()
            ->groups([
                Group::make('category.group')
                    ->label('')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn(CategoryStatistic $record): string => __('category.groups')[$record->category->group->name])
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
                    ->selectablePlaceholder(false)
                    ->default(Carbon::today()->year)
            ], Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(1)
            ->persistFiltersInSession()
            ->emptyStateHeading('');
    }

    private static function formatNumber($number)
    {
        $numberStr = (string)$number;
        $decimalPart = rtrim(substr($numberStr, strpos($numberStr, '.') + 1), '0');
        return max(strlen($decimalPart), 2);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoryStatistics::route('/'),
        ];
    }
}
