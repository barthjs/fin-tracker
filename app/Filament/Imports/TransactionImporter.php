<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\TransactionService;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

final class TransactionImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            self::dateTimeColumn(),

            self::typeColumn()
                ->fillRecordUsing(function (Transaction $record, string $state): void {
                    $record->type = match ($state) {
                        TransactionType::Expense->getLabel() => TransactionType::Expense,
                        TransactionType::Revenue->getLabel() => TransactionType::Revenue,
                        TransactionType::Transfer->getLabel() => TransactionType::Transfer,
                        default => TransactionType::Expense,
                    };
                }),

            ImportColumn::make('transfer_account_id')
                ->label(__('account.fields.transfer_account_id'))
                ->fillRecordUsing(function (Transaction $record, ?string $state): void {
                    if ($state === null) {
                        return;
                    }

                    $account = Account::whereName($state);
                    if ($account->count() > 1) {
                        $record->transfer_account_id = Account::getOrCreateDefaultAccount()->id;
                    } else {
                        $record->transfer_account_id = $account->first()->id ?? Account::getOrCreateDefaultAccount()->id;
                    }
                }),

            self::numericColumn('amount')
                ->label(__('transaction.fields.amount')),

            ImportColumn::make('payee')
                ->label(__('transaction.fields.payee'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('category_id')
                ->label(Str::ucfirst(__('category.label')))
                ->fillRecordUsing(function (Transaction $record, ?string $state, array $data): void {
                    $query = Category::whereName($state);

                    // Try to find the category by name and group
                    $group = array_key_exists('group', $data) ? match ($data['group']) {
                        CategoryGroup::FixExpenses->getLabel() => CategoryGroup::FixExpenses,
                        CategoryGroup::VarExpenses->getLabel() => CategoryGroup::VarExpenses,
                        CategoryGroup::FixRevenues->getLabel() => CategoryGroup::FixRevenues,
                        CategoryGroup::VarRevenues->getLabel() => CategoryGroup::VarRevenues,
                        CategoryGroup::Transfers->getLabel() => CategoryGroup::Transfers,
                        default => null
                    } : null;

                    if ($group !== null) {
                        $query->where('group', $group);
                    }

                    $result = $query->get();
                    if ($result->count() > 1) {
                        $record->category_id = Category::getOrCreateDefaultCategory()->id;
                    } else {
                        $record->category_id = $result->first()->id ?? Category::getOrCreateDefaultCategory()->id;
                    }
                }),

            // Don't import the group
            ImportColumn::make('group')
                ->label(__('category.fields.group'))
                ->fillRecordUsing(function (Transaction $record, string $state): void {}),

            self::notesColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('transaction.import.body_heading')."\n\r".
            __('transaction.import.body_success').number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r".__('transaction.import.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function resolveRecord(): Transaction
    {
        return new Transaction;
    }

    public function saveRecord(): void
    {
        $service = app(TransactionService::class);
        /** @phpstan-ignore-next-line  */
        $this->record = $service->create($this->record->toArray());
    }

    public function getJobBatchName(): string
    {
        return 'transaction-import';
    }
}
