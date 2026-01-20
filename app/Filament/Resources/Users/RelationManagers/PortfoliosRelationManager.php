<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Portfolios\PortfolioResource;
use App\Models\Portfolio;
use BackedEnum;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class PortfoliosRelationManager extends RelationManager
{
    use HasResourceActions;

    protected static string $relationship = 'portfolios';

    protected static string|BackedEnum|null $icon = 'tabler-wallet';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return Str::ucfirst(__('portfolio.plural_label'));
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        /** @var Portfolio $ownerRecord */
        return (string) Portfolio::withoutGlobalScopes()->where('user_id', $ownerRecord->id)->count();
    }

    public function form(Schema $schema): Schema
    {
        return PortfolioResource::form($schema);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        /** @var string $userId */
        $userId = $this->getOwnerRecord()->id ?? auth()->id();

        return PortfolioResource::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->headerActions([
                self::tableCreateAction()
                    ->mutateDataUsing(function (array $data) use ($userId): array {
                        $data['user_id'] = $userId;

                        return $data;
                    }),
            ]);
    }
}
