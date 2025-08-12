<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortfolioResource\RelationManagers;

use App\Filament\Resources\TradeResource;
use BackedEnum;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TradesRelationManager extends RelationManager
{
    protected static string $relationship = 'trades';

    protected static string|BackedEnum|null $icon = 'tabler-exchange';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('trade.navigation_label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(TradeResource::formParts(portfolio: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return TradeResource::table($table)
            ->heading('');
    }
}
