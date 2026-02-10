<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property Category $ownerRecord
 */
final class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static string|BackedEnum|null $icon = 'tabler-credit-card';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return Str::ucfirst(__('transaction.plural_label'));
    }

    public function form(Schema $schema): Schema
    {
        return TransactionResource::form($schema, category: $this->ownerRecord);
    }

    public function table(Table $table): Table
    {
        return TransactionResource::table($table);
    }
}
