<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Securities\SecurityResource;
use App\Models\Security;
use BackedEnum;
use Exception;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class SecuritiesRelationManager extends RelationManager
{
    protected static string $relationship = 'securities';

    protected static string|BackedEnum|null $icon = 'tabler-file-percent';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('security.plural_label');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        /** @var Security $ownerRecord */
        return (string) Security::withoutGlobalScopes()->where('user_id', $ownerRecord->id)->count();
    }

    public function form(Schema $schema): Schema
    {
        return SecurityResource::form($schema);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        /** @var string $userId */
        $userId = $this->getOwnerRecord()->id ?? auth()->id();

        return SecurityResource::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->paginated(fn (): bool => Security::withoutGlobalScopes()->where('user_id', $userId)->count() > 20)
            ->heading(null)
            ->modelLabel(__('security.label'))
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
