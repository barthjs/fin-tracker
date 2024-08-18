<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\Currency;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BankAccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'bankAccounts';
    protected static ?string $icon = 'tabler-bank-building';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->required()
                    ->string(),
                Forms\Components\Select::make('currency')
                    ->options(Currency::class)
                    ->required()
                    ->searchable(),
                Forms\Components\Textarea::make('description')
                    ->autosize()
                    ->maxLength(1000)
                    ->rows(1)
                    ->string()
                    ->grow(),
                Forms\Components\Toggle::make('active')
                    ->default(true)
                    ->inline(false)
            ])
            ->columns(4);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->sortable()
                    ->toggleable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('active')
                    ->query(fn($query) => $query->where('active', true))
            ])
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes());
    }
}
