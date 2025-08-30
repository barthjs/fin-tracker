<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accounts\RelationManagers;

use App\Filament\Resources\Transactions\TransactionResource;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class TransactionRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static string|BackedEnum|null $icon = 'tabler-credit-card';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('transaction.plural_label');
    }

    public function form(Schema $schema): Schema
    {
        return TransactionResource::form($schema, account: $this->ownerRecord);
    }

    public function table(Table $table): Table
    {
        return TransactionResource::table($table)
            ->heading(null)
            ->modelLabel(__('transaction.label'));
    }
}
