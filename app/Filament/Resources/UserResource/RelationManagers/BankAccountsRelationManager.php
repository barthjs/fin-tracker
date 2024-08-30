<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\BankAccountResource;
use App\Models\BankAccount;
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
        return __('bank_account.navigation_label');
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
            ->paginated(fn() => BankAccount::all()->count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->query(fn($query) => $query->where('active', false))
            ])
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->modalHeading(__('bank_account.buttons.edit_heading')),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading(__('bank_account.buttons.delete_heading'))
            ])
            ->bulkActions(BankAccountResource::getBulkActions())
            ->emptyStateHeading(__('bank_account.empty'))
            ->emptyStateDescription('')
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes());
    }
}
