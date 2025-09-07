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
use Filament\Resources\Resource;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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

    public static function getWidgets(): array
    {
        return [
            CategoryStatisticChart::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $query->whereHas('category', function (Builder $query): void {
                    $query->where('type', '!=', TransactionType::Transfer)->where('is_active', true);
                });

                return $query;
            })
            ->deferLoading()
            ->columns(self::getTableColumns())
            ->paginated(false)
            ->defaultSort('year', 'desc')
            ->defaultGroup('category.group')
            ->groupingSettingsHidden()
            ->groups([
                Group::make('category.group')
                    ->label('')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (CategoryStatistic $record): string => $record->category->group->getLabel().' '.$record->year),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label(__('table.filter.year'))
                    ->options(function (): array {
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
                    ->default(Carbon::now()->year),
            ])
            ->recordActions([])
            ->recordUrl(fn (CategoryStatistic $record): string => ViewCategory::getUrl(['record' => $record->category_id]));
    }

    /**
     * @return array<int, Column>
     */
    public static function getTableColumns(): array
    {
        return array_merge(
            [
                TextColumn::make('category.name')
                    ->label(Str::ucfirst(__('category.label')))
                    ->wrap(),
            ],
            array_map(function (string $month): TextColumn {
                return TextColumn::make($month)
                    ->label(__("category_statistic.fields.$month"))
                    ->alignEnd()
                    ->numeric(2)
                    ->summarize(Sum::make()->money(Currency::getCurrency()))
                    ->toggleable();
            }, CategoryStatistic::MONTHS)
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoryStatistics::route('/'),
        ];
    }
}
