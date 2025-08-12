<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Accounts\AccountResource;
use App\Models\Account;
use BackedEnum;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'accounts';

    protected static string|BackedEnum|null $icon = 'tabler-bank-building';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('account.navigation_label');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) Account::withoutGlobalScopes()->whereUserId($ownerRecord->id)->count();
    }

    public function form(Schema $schema): Schema
    {
        return AccountResource::form($schema);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $columns = AccountResource::tableColumns();

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->heading('')
            ->columns($columns)
            ->paginated(fn (): bool => Account::withoutGlobalScopes()->whereUserId($this->getOwnerRecord()->id)->count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('active', false)),
            ])
            ->persistFiltersInSession()
            ->headerActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('account.buttons.create_button_label'))
                    ->modalHeading(__('account.buttons.create_heading'))
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->modalHeading(__('account.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('account.buttons.delete_heading'))
                    ->disabled(fn (Account $record): bool => $record->transactions()->withoutGlobalScopes()->exists() || $record->trades()->withoutGlobalScopes()->exists()),
            ])
            ->toolbarActions(AccountResource::getBulkActions())
            ->emptyStateHeading(__('account.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('account.buttons.create_button_label'))
                    ->modalHeading(__('account.buttons.create_heading'))
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ]);
    }
}
