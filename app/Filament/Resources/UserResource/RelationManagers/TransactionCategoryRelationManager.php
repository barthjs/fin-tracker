<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\TransactionCategoryResource;
use App\Models\TransactionCategory;
use Exception;
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
        return TransactionCategoryResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $tableParts = TransactionCategoryResource::tableColumns();
        return $table
            ->heading('')
            ->columns($tableParts)
            ->paginated(fn() => TransactionCategory::all()->count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('resources.transaction_categories.create_label'))
                    ->modalHeading(__('resources.transaction_categories.create_heading'))
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes());
    }
}
