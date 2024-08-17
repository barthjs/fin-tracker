<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionCategoryRelationManager extends RelationManager
{
    protected static string $relationship = 'transactionCategory';
    protected static ?string $icon = 'heroicon-o-rectangle-stack';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->autofocus()
                    ->maxLength(255)
                    ->required()
                    ->string(),
                Forms\Components\Toggle::make('active')
                    ->default(true)
                    ->inline(false),
                Forms\Components\Select::make('type')
                    ->options(TransactionType::class)
                    ->required(),
                Forms\Components\Select::make('group')
                    ->options(TransactionGroup::class)
                    ->required(),
            ])
            ->columns(2);
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
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('group')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y.m.d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('Y.m.d H:i:s')
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
