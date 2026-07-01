<?php

declare(strict_types=1);

namespace App\Filament\Resources\Budgets\RelationManagers;

use App\Actions\CalculateBudgetUsage;
use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Budget;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Lists the expenses that count towards the budget's current period.
 *
 * @property Budget $ownerRecord
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
        return TransactionResource::form($schema, category: $this->ownerRecord->category);
    }

    public function table(Table $table): Table
    {
        $usage = resolve(CalculateBudgetUsage::class)($this->ownerRecord);

        return TransactionResource::table($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->where('type', TransactionType::Expense)
                ->where('date_time', '>=', $usage->periodStart->setTimezone('UTC'))
                ->where('date_time', '<', $usage->periodEnd->setTimezone('UTC')));
    }
}
