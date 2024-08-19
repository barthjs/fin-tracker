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
use Illuminate\Database\Eloquent\Model;

class BankAccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'bankAccounts';
    protected static ?string $icon = 'tabler-bank-building';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.bank_accounts.navigation_label');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('resources.bank_accounts.table.name'))
                    ->maxLength(255)
                    ->required()
                    ->string(),
                Forms\Components\Select::make('currency')
                    ->label(__('resources.bank_accounts.table.currency'))
                    ->placeholder(__('resources.bank_accounts.form.currency_placeholder'))
                    ->options(Currency::class)
                    ->required()
                    ->searchable(),
                Forms\Components\Textarea::make('description')
                    ->label(__('tables.description'))
                    ->autosize()
                    ->maxLength(1000)
                    ->rows(1)
                    ->string()
                    ->grow(),
                Forms\Components\Toggle::make('active')
                    ->label(__('tables.active'))
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
            ->heading('')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.bank_accounts.table.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label(__('resources.bank_accounts.table.currency'))
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('tables.description'))
                    ->sortable()
                    ->toggleable()
                    ->wrap(),
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
            ->emptyStateHeading(__('resources.bank_accounts.table.empty'))
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()
                    ->modalHeading(__('resources.bank_accounts.edit_heading')),
                Tables\Actions\DeleteAction::make()->iconButton()
                    ->modalHeading(__('resources.bank_accounts.delete_heading')),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes());
    }
}
