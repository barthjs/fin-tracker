<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
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

class CategoryRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static string|BackedEnum|null $icon = 'tabler-category';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('category.navigation_label');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) Category::withoutGlobalScopes()->whereUserId($ownerRecord->id)->count();
    }

    public function form(Schema $schema): Schema
    {
        return CategoryResource::form($schema);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        $tableParts = CategoryResource::tableColumns();

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->heading('')
            ->columns($tableParts)
            ->paginated(fn (): bool => Category::withoutGlobalScopes()->whereUserId($this->getOwnerRecord()->id)->count() > 20)
            ->defaultSort('name')
            ->persistSortInSession()
            ->striped()
            ->filters([
                Filter::make('inactive')
                    ->label(__('table.status_inactive'))
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('active', false)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('category.buttons.create_button_label'))
                    ->modalHeading(__('category.buttons.create_heading'))
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->persistFiltersInSession()
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->modalHeading(__('category.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('category.buttons.delete_heading'))
                    ->disabled(fn (Category $record): bool => $record->transactions()->withoutGlobalScopes()->exists()),
            ])
            ->toolbarActions(CategoryResource::getBulkActions())
            ->emptyStateHeading(__('category.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('category.buttons.create_button_label'))
                    ->modalHeading(__('category.buttons.create_heading'))
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ]);
    }
}
