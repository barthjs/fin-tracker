<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions\RelationManagers;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Subscription;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property Subscription $ownerRecord
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
        return TransactionResource::form($schema, account: $this->ownerRecord->account, category: $this->ownerRecord->category, subscription: $this->ownerRecord);
    }

    public function table(Table $table): Table
    {
        return TransactionResource::table($table)
            ->heading(Str::ucfirst(__('transaction.plural_label')))
            ->filtersLayout(FiltersLayout::Dropdown)
            ->filtersFormColumns(1);
    }
}
