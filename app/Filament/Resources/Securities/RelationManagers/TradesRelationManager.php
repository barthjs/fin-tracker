<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities\RelationManagers;

use App\Filament\Resources\Trades\TradeResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Str;

final class TradesRelationManager extends RelationManager
{
    protected static string $relationship = 'trades';

    public function form(Schema $schema): Schema
    {
        return TradeResource::form($schema, security: $this->ownerRecord);
    }

    public function table(Table $table): Table
    {
        return TradeResource::table($table)
            ->heading(Str::ucfirst(__('trade.plural_label')));
    }
}
