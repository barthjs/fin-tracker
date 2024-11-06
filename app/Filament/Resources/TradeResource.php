<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TradeResource\Pages\ListTrades;
use App\Models\Trade;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TradeResource extends Resource
{
    protected static ?string $model = Trade::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema(self::formParts());
    }

    public static function formParts(): array
    {
        return [
            Section::make()
                ->schema([
                    DateTimePicker::make('date_time')
                        ->required(),
                    TextInput::make('total_amount')
                        ->required()
                        ->numeric()
                        ->default(0.000000),
                    TextInput::make('quantity')
                        ->required()
                        ->numeric(),
                    TextInput::make('price')
                        ->required()
                        ->numeric(),
                    TextInput::make('tax')
                        ->required()
                        ->numeric()
                        ->default(0),
                    TextInput::make('fee')
                        ->required()
                        ->numeric()
                        ->default(0),
                    TextInput::make('notes')
                        ->maxLength(255)
                        ->default(null),
                    Select::make('account_id')
                        ->relationship('account', 'name')
                        ->required(),
                    Select::make('portfolio_id')
                        ->relationship('portfolio', 'name')
                        ->required(),
                    Select::make('security_id')
                        ->relationship('security', 'name')
                        ->required(),
                ])
                ->columns(2)
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_time')
                    ->dateTime('Y-m-d, H:i')
                    ->fontFamily('mono')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total_amount')
                    ->fontFamily('mono')
                    ->numeric(2)
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->fontFamily('mono')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->fontFamily('mono')
                    ->money()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tax')
                    ->fontFamily('mono')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('fee')
                    ->fontFamily('mono')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('notes')
                    ->toggleable(),
                ImageColumn::make('account.logo')
                    ->label('')
                    ->circular()
                    ->alignEnd(),
                TextColumn::make('account.name')
                    ->hiddenOn(AccountResource\RelationManagers\TransactionRelationManager::class)
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('portfolio.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('security.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->paginated(fn(): bool => Trade::count() > 20)
            ->deferLoading()
            ->extremePaginationLinks()
            ->defaultSort('date_time', 'desc')
            ->persistSortInSession()
            ->striped()
            ->filters([

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                CreateAction::make('header-create')
                    ->icon('tabler-plus')
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrades::route('/'),
        ];
    }
}
