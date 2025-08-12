<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Filament\Resources\TransactionResource;
use BackedEnum;
use Exception;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static string|BackedEnum|null $icon = 'tabler-credit-card';

    public function form(Schema $schema): Schema
    {
        return $schema->components(TransactionResource::formParts(category: $this->ownerRecord));
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return TransactionResource::table($table)
            ->heading(__('transaction.navigation_label'));
    }
}
