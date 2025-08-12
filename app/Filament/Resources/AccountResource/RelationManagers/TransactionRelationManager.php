<?php

declare(strict_types=1);

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Filament\Resources\TransactionResource;
use BackedEnum;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static string|BackedEnum|null $icon = 'tabler-credit-card';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('transaction.navigation_label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components(TransactionResource::formParts(account: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return TransactionResource::table($table)
            ->heading('');
    }
}
