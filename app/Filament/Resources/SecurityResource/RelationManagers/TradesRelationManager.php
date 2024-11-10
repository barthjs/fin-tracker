<?php

namespace App\Filament\Resources\SecurityResource\RelationManagers;

use App\Filament\Resources\TradeResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TradesRelationManager extends RelationManager
{
    protected static string $relationship = 'trades';

    public function form(Form $form): Form
    {
        return $form->schema(TradeResource::formParts(security: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return TradeResource::table($table)
            ->heading(__('trade.navigation_label'));
    }

    /**
     * Editable on the list poge
     */
    public function isReadOnly(): bool
    {
        return false;
    }
}
