<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use BackedEnum;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class CategoriesRelationManager extends RelationManager
{
    use HasResourceActions;

    protected static string $relationship = 'categories';

    protected static string|BackedEnum|null $icon = 'tabler-category';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return Str::ucfirst(__('category.plural_label'));
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
            ->headerActions([
                self::createAction()
                    ->mutateDataUsing(function (array $data) use ($userId): array {
                        $data['user_id'] = $userId;

                        return $data;
                    }),
            ]);
    }
}
