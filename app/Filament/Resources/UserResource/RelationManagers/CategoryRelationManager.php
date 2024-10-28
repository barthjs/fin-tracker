<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CategoryRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';
    protected static ?string $icon = 'tabler-category';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('transaction_category.navigation_label');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return Category::withoutGlobalScopes()->whereUserId($ownerRecord->id)->count();
    }

    public function form(Form $form): Form
    {
        return CategoryResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $tableParts = CategoryResource::tableColumns();
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes())
            ->heading('')
            ->columns($tableParts)
            ->paginated(fn() => Category::withoutGlobalScopes()->whereUserId($this->getOwnerRecord()->id)->count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->toggle()
                    ->query(fn(Builder $query) => $query->where('active', false)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('transaction_category.buttons.create_button_label'))
                    ->modalHeading(__('transaction_category.buttons.create_heading'))
                    ->mutateFormDataUsing(function (array $data) {
                        $data['user_id'] = $this->getOwnerRecord()->id;
                        return $data;
                    })
            ])
            ->persistFiltersInSession()
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->modalHeading(__('transaction_category.buttons.edit_heading')),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->modalHeading(__('transaction_category.buttons.delete_heading'))
            ])
            ->bulkActions(CategoryResource::getBulkActions())
            ->emptyStateHeading(__('transaction_category.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('transaction_category.buttons.create_button_label'))
                    ->modalHeading(__('transaction_category.buttons.create_heading'))
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
