<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\AccountResource;
use App\Models\Account;
use App\Models\Category;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'accounts';
    protected static ?string $icon = 'tabler-bank-building';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('bank_account.navigation_label');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return Account::withoutGlobalScopes()->whereUserId($ownerRecord->id)->count();
    }

    public function form(Form $form): Form
    {
        return AccountResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $columns = AccountResource::tableColumns();
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes())
            ->heading('')
            ->columns($columns)
            ->paginated(fn() => Account::withoutGlobalScopes()->whereUserId($this->getOwnerRecord()->id)->count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->toggle()
                    ->query(fn(Builder $query) => $query->where('active', false)),
            ])
            ->persistFiltersInSession()
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('bank_account.buttons.create_button_label'))
                    ->modalHeading(__('bank_account.buttons.create_heading'))
                    ->mutateFormDataUsing(function (array $data) {
                        $data['user_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->modalHeading(__('bank_account.buttons.edit_heading')),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading(__('bank_account.buttons.delete_heading'))
            ])
            ->bulkActions(AccountResource::getBulkActions())
            ->emptyStateHeading(__('bank_account.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('bank_account.buttons.create_button_label'))
                    ->modalHeading(__('bank_account.buttons.create_heading'))
                    ->mutateFormDataUsing(function (array $data) {
                        $data['user_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    })
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
