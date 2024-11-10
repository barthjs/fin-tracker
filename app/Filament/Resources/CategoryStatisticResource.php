<?php

namespace App\Filament\Resources;

use App\Enums\TransactionType;
use App\Filament\Resources\CategoryResource\Pages\ViewCategory;
use App\Filament\Resources\CategoryStatisticResource\Pages\ListCategoryStatistics;
use App\Models\Account;
use App\Models\CategoryStatistic;
use Exception;
use Filament\Resources\Resource;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
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
            ->modifyQueryUsing(function (Builder $query, Table $table) {
                $query->whereHas('category', function (Builder $query) {
                    $query->where('type', '!=', TransactionType::transfer)->where('active', '=', true);
                });
                if (!$table->getActiveFiltersCount()) {
                    return $query->where('year', '=', Carbon::now()->year);
                } else {
                    return $query;
                }
            })
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('transaction.columns.category'))
                    ->wrap(),
                TextColumn::make('jan')
                    ->label(__('category_statistic.columns.jan'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('feb')
                    ->label(__('category_statistic.columns.feb'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('mar')
                    ->label(__('category_statistic.columns.mar'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('apr')
                    ->label(__('category_statistic.columns.apr'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('may')
                    ->label(__('category_statistic.columns.may'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('jun')
                    ->label(__('category_statistic.columns.jun'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('jul')
                    ->label(__('category_statistic.columns.jul'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('aug')
                    ->label(__('category_statistic.columns.aug'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('sep')
                    ->label(__('category_statistic.columns.sep'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('oct')
                    ->label(__('category_statistic.columns.oct'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('nov')
                    ->label(__('category_statistic.columns.nov'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
                TextColumn::make('dec')
                    ->label(__('category_statistic.columns.dec'))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->label('')->money(Account::getCurrency(), 100))
                    ->toggleable(),
            ])
            ->paginated(false)
            ->searchable(false)
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
            ], FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(1)
            ->persistFiltersInSession()
            ->recordUrl(fn(CategoryStatistic $record): string => ViewCategory::getUrl([$record->category_id]))
            ->emptyStateHeading('');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoryStatistics::route('/'),
        ];
    }
}
