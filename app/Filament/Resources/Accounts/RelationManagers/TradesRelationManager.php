<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts\RelationManagers;

use App\Filament\Resources\Trades\TradeResource;
use App\Models\Account;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property Account $ownerRecord
 */
final class TradesRelationManager extends RelationManager
{
    protected static string $relationship = 'trades';

    protected static string|BackedEnum|null $icon = 'tabler-exchange';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return Str::ucfirst(__('trade.plural_label'));
    }

    public function form(Schema $schema): Schema
    {
        return TradeResource::form($schema, account: $this->ownerRecord);
    }

    public function table(Table $table): Table
    {
        return TradeResource::table($table);
    }
}
