<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subscriptions\RelationManagers;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Subscription;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * @property Subscription $ownerRecord
 */
final class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

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
