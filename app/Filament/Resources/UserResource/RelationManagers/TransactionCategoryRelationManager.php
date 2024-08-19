<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TransactionCategoryRelationManager extends RelationManager
{
    protected static string $relationship = 'transactionCategory';
    protected static ?string $icon = 'tabler-category';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.transaction_categories.navigation_label');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('resources.transaction_categories.table.name'))
                    ->autofocus()
                    ->maxLength(255)
                    ->required()
                    ->string(),
                Forms\Components\Toggle::make('active')
                    ->default(true)
                    ->inline(false),
                Forms\Components\Select::make('type')
                    ->label(__('resources.transaction_categories.table.type'))
                    ->placeholder(__('resources.transaction_categories.form.type_placeholder'))
                    ->options(__('resources.transaction_categories.types'))
                    ->required(),
                Forms\Components\Select::make('group')
                    ->label(__('resources.transaction_categories.table.type'))
                    ->placeholder(__('resources.transaction_categories.form.group_placeholder'))
                    ->options(__('resources.transaction_categories.groups'))
                    ->required(),
            ]);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->heading('')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.transaction_categories.table.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('resources.transaction_categories.table.type'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('group')
                    ->label(__('resources.transaction_categories.table.group'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->label(__('tables.active'))
                    ->boolean()
                    ->sortable()
                    ->tooltip(fn($state): string => $state ? __('tables.status_active') : 'tables.status_inactive')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('tables.created_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('tables.updated_at'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('active')
                    ->label(__('tables.status_active'))
                    ->query(fn($query) => $query->where('active', true)),
                Filter::make('inactive')
                    ->label(__('tables.status_inactive'))
                    ->query(fn($query) => $query->where('active', false))
            ])
            ->persistFiltersInSession()
            ->emptyStateHeading(__('resources.transaction_categories.table.empty'))
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()
                    ->modalHeading(__('resources.transaction_categories.edit_heading')),
                Tables\Actions\DeleteAction::make()->iconButton()
                    ->modalHeading(__('resources.transaction_categories.delete_heading')),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes());
    }
}
