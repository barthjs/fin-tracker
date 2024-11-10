<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Filament\Resources\TradeResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TradesRelationManager extends RelationManager
{
    protected static string $relationship = 'trades';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('trade.navigation_label');
    }

    public function form(Form $form): Form
    {
        return $form->schema(TradeResource::formParts(account: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return TradeResource::table($table)
            ->heading('');
    }

    /**
     * Editable on the list poge
     */
    public function isReadOnly(): bool
    {
        return false;
    }
}
