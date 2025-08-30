<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Accounts\AccountResource;
use App\Models\Account;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class AccountsRelationManager extends RelationManager
{
    use HasResourceActions;

    protected static string $relationship = 'accounts';

    protected static string|BackedEnum|null $icon = 'tabler-bank-building';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('account.plural_label');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): string
    {
        /** @var Account $ownerRecord */
        return (string) Account::withoutGlobalScopes()->where('user_id', $ownerRecord->id)->count();
    }

    public function form(Schema $schema): Schema
    {
        return AccountResource::form($schema);
    }

    public function table(Table $table): Table
    {
        /** @var string $userId */
        $userId = $this->getOwnerRecord()->id ?? auth()->id();

        return AccountResource::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->paginated(fn (): bool => Account::withoutGlobalScopes()->where('user_id', $userId)->count() > 20)
            ->heading(null)
            ->modelLabel(__('account.label'))
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
