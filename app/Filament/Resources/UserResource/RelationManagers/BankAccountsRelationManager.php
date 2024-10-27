<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\BankAccountResource;
use App\Models\BankAccount;
use App\Models\TransactionCategory;
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

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return BankAccount::count();
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
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes())
            ->heading('')
            ->columns($columns)
            ->paginated(fn() => BankAccount::count() > 20)
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
            ->bulkActions(BankAccountResource::getBulkActions())
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
