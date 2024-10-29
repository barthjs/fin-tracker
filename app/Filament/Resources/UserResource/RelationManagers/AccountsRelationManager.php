<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\AccountResource;
use App\Models\Account;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
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
        return __('account.navigation_label');
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
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->withoutGlobalScopes())
            ->heading('')
            ->columns($columns)
            ->paginated(fn(): bool => Account::withoutGlobalScopes()->whereUserId($this->getOwnerRecord()->id)->count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->toggle()
                    ->query(fn(Builder $query): Builder => $query->where('active', false)),
            ])
            ->persistFiltersInSession()
            ->headerActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('account.buttons.create_button_label'))
                    ->modalHeading(__('account.buttons.create_heading'))
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    })
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->modalHeading(__('account.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('account.buttons.delete_heading'))
            ])
            ->bulkActions(AccountResource::getBulkActions())
            ->emptyStateHeading(__('account.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('account.buttons.create_button_label'))
                    ->modalHeading(__('account.buttons.create_heading'))
                    ->mutateFormDataUsing(function (array $data): array {
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
