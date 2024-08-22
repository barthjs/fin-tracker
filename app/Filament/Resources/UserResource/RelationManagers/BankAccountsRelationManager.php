<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\BankAccountResource;
use Exception;
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
        return BankAccountResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $columns = BankAccountResource::tableColumns();
        return $table
            ->heading('')
            ->columns($columns)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('resources.bank_accounts.create_label'))
                    ->modalHeading(__('resources.bank_accounts.create_heading'))
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes());
    }
}
