<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\PortfolioResource;
use App\Models\Portfolio;
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

class PortfoliosRelationManager extends RelationManager
{
    protected static string $relationship = 'portfolios';

    protected static string|BackedEnum|null $icon = 'tabler-wallet';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('portfolio.navigation_label');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) Portfolio::withoutGlobalScopes()->whereUserId($ownerRecord->id)->count();
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
        $columns = PortfolioResource::getTableColumns();

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->heading('')
            ->columns($columns)
            ->paginated(fn (): bool => Portfolio::withoutGlobalScopes()->whereUserId($this->getOwnerRecord()->id)->count() > 20)
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
                    ->label(__('portfolio.buttons.create_button_label'))
                    ->modalHeading(__('portfolio.buttons.create_heading'))
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->icon('tabler-edit')
                    ->modalHeading(__('portfolio.buttons.edit_heading')),
                DeleteAction::make()
                    ->iconButton()
                    ->icon('tabler-trash')
                    ->modalHeading(__('portfolio.buttons.delete_heading'))
                    ->disabled(fn (Portfolio $record): bool => $record->trades()->withoutGlobalScopes()->exists()),
            ])
            ->emptyStateHeading(__('portfolio.empty'))
            ->emptyStateDescription('')
            ->emptyStateActions([
                CreateAction::make()
                    ->icon('tabler-plus')
                    ->label(__('portfolio.buttons.create_button_label'))
                    ->modalHeading(__('portfolio.buttons.create_heading'))
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ]);
    }
}
