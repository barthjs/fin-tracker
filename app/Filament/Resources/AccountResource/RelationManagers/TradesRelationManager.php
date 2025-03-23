<?php

declare(strict_types=1);

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

    protected static ?string $icon = 'tabler-exchange';

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
}
