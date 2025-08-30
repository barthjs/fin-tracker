<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use BackedEnum;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class CategoryRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static string|BackedEnum|null $icon = 'tabler-category';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('category.plural_label');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        /** @var Category $ownerRecord */
        return (string) Category::withoutGlobalScopes()->where('user_id', $ownerRecord->id)->count();
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
        /** @var string $userId */
        $userId = $this->getOwnerRecord()->id ?? auth()->id();

        return CategoryResource::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->paginated(fn (): bool => Category::withoutGlobalScopes()->where('user_id', $userId)->count() > 20)
            ->heading(null)
            ->modelLabel(__('category.label'))
            ->headerActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->mutateDataUsing(function (array $data) use ($userId): array {
                        $data['user_id'] = $userId;

                        return $data;
                    }),
            ])
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->mutateDataUsing(function (array $data) use ($userId): array {
                        $data['user_id'] = $userId;

                        return $data;
                    }),
            ]);
    }
}
