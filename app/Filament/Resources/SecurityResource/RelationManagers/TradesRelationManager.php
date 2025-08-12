<?php

declare(strict_types=1);

namespace App\Filament\Resources\SecurityResource\RelationManagers;

use App\Filament\Resources\TradeResource;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TradesRelationManager extends RelationManager
{
    protected static string $relationship = 'trades';

    public function form(Schema $schema): Schema
    {
        return $schema->components(TradeResource::formParts(security: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return TradeResource::table($table)
            ->heading(__('trade.navigation_label'));
    }
}
