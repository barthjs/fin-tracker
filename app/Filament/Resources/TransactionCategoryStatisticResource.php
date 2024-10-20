<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionCategoryStatisticResource\Pages;
use App\Filament\Resources\TransactionCategoryStatisticResource\RelationManagers;
use App\Models\TransactionCategoryStatistic;
use Exception;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class TransactionCategoryStatisticResource extends Resource
{
    protected static ?string $model = TransactionCategoryStatistic::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'tabler-calendar-stats';

    public static function getSlug(): string
    {
        return 'statistics';
    }

    public static function getNavigationLabel(): string
    {
        return 'Statistics';
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('bank_account_transaction.columns.category'))
                    ->copyable()
                    ->copyMessage(__('table.copied'))
                    ->wrap(),
                Tables\Columns\TextColumn::make('jan')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->summarize(Sum::make()->label('Total'))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('feb')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('mar')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('apr')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('may')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jun')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jul')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('aug')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sep')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('oct')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('nov')
                    ->numeric(fn($state) => self::formatNumber($state))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('dec')
                    ->numeric(fn($state) => self::formatNumber($state))
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
                    ->getTitleFromRecordUsing(fn(TransactionCategoryStatistic $record): string => __('transaction_category.groups')[$record->category->group->name])
            ])
            ->striped()
            ->filters([
                SelectFilter::make('year')
                    ->options(fn(): array => TransactionCategoryStatistic::select('year')
                        ->distinct()
                        ->orderBy('year', 'desc')
                        ->pluck('year', 'year')->toArray()
                    )
                    ->selectablePlaceholder(false)
                    ->default(Carbon::today()->year)
            ], Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(1)
            ->persistFiltersInSession();
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
            'index' => Pages\ListTransactionCategoryStatistics::route('/'),
        ];
    }
}
