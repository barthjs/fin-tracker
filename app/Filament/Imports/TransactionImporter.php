<?php declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Tools\Convertor;
use Exception;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;

class TransactionImporter extends Importer
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('date_time')
                ->label(__('transaction.columns.date'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (Transaction $record, string $state): void {
                    try {
                        $carbon = Carbon::parse($state);
                    } catch (Exception) {
                        $carbon = Carbon::now();
                    }
                    $record->date_time = $carbon;
                }),
            ImportColumn::make('account_id')
                ->label(__('transaction.columns.account'))
                ->fillRecordUsing(function (Transaction $record, string $state): void {
                    $account = Account::whereName($state);
                    if ($account->count() > 1) {
                        $record->account_id = null;
                    } else {
                        $record->account_id = $account->first()->id ?? null;
                    }
                }),
            ImportColumn::make('amount')
                ->label(__('transaction.columns.amount'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn(Transaction $record, string $state) => $record->amount = Convertor::formatNumber($state)),
            ImportColumn::make('destination')
                ->label(__('transaction.columns.destination'))
                ->rules(['max:255']),
            ImportColumn::make('category_id')
                ->label(__('transaction.columns.category'))
                ->fillRecordUsing(function (Transaction $record, string $state, $data): void {
                    $group = array_key_exists('group', $data) ? match ($data['group']) {
                        __('category.groups.fix_expenses') => 'fix_expenses',
                        __('category.groups.var_expenses') => 'var_expenses',
                        __('category.groups.fix_revenues') => 'fix_revenues',
                        __('category.groups.var_revenues') => 'var_revenues',
                        __('category.groups.transfers') => 'transfers',
                        default => ''
                    } : '';

                    $query = Category::whereName($state);

                    if ($group) {
                        $query->whereGroup($group);
                    }

                    $result = $query->get();
                    if ($result->count() > 1) {
                        $record->category_id = null;
                    } else {
                        $record->category_id = $result->first()->id ?? null;
                    }
                }),
            ImportColumn::make('group')
                ->label(__('transaction.columns.group'))
                ->fillRecordUsing(function (Transaction $record, string $state): void {
                }),
            ImportColumn::make('notes')
                ->label(__('transaction.columns.notes'))
                ->rules(['max:255'])];
    }

    public function resolveRecord(): ?Transaction
    {
        return new Transaction();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('transaction.notifications.import.body_heading') . "\n\r" .
            __('transaction.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('transaction.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'transaction-import';
    }
}
