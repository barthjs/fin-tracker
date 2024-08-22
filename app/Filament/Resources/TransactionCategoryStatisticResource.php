<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionCategoryStatisticResource\Pages;
use App\Filament\Resources\TransactionCategoryStatisticResource\RelationManagers;
use App\Models\TransactionCategoryStatistic;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionCategoryStatisticResource extends Resource
{
    protected static ?string $model = TransactionCategoryStatistic::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('feb')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mar')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('apr')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('may')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jun')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jul')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('aug')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sep')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('oct')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nov')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dec')
                    ->numeric()
                    ->sortable(),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->where('year', Carbon::today()->year);
            })
            ->defaultSort('category.type')
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactionCategoryStatistics::route('/'),
        ];
    }
}
