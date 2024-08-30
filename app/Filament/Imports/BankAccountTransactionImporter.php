<?php

namespace App\Filament\Imports;

use App\Models\BankAccount;
use App\Models\BankAccountTransaction;
use App\Models\TransactionCategory;
use Exception;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;

class BankAccountTransactionImporter extends Importer
{
    protected static ?string $model = BankAccountTransaction::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('date_time')
                ->label(__('bank_account_transaction.columns.date'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (BankAccountTransaction $record, string $state): void {
                    try {
                        $carbon = Carbon::parse($state)->toDateTimeString();
                    } catch (Exception) {
                        $carbon = Carbon::now()->toDateTimeString();
                    }
                    $record->date_time = $carbon;
                }),
            ImportColumn::make('bank_account_id')
                ->label(__('bank_account_transaction.columns.account'))
                ->fillRecordUsing(function (BankAccountTransaction $record, string $state): void {
                    $bankAccount = BankAccount::whereName($state);
                    if ($bankAccount->count() > 1) {
                        $record->bank_account_id = null;
                    } else {
                        $record->bank_account_id = $bankAccount->first()->id ?? null;
                    }
                }),
            ImportColumn::make('amount')
                ->label(__('bank_account_transaction.columns.amount'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (BankAccountTransaction $record, string $state): void {
                    // Sanitize the input by removing all characters except digits, commas, periods, and signs.
                    $sanitized = preg_replace('/[^0-9,.+-]/', '', $state);

                    if (empty($sanitized) || $sanitized === '-' || $sanitized === '+') {
                        // Handle cases where the input is empty or just a sign.
                        $floatValue = 0.0;
                    } else {
                        $sign = 1;
                        if (str_starts_with($sanitized, '-')) {
                            $sign = -1; // Set sign for negative numbers
                            $sanitized = substr($sanitized, 1);
                        } elseif (str_starts_with($sanitized, '+')) {
                            $sanitized = substr($sanitized, 1);
                        }

                        // Handle different formats with both period and comma present.
                        if (str_contains($sanitized, '.') && str_contains($sanitized, ',')) {
                            if (strrpos($sanitized, '.') < strrpos($sanitized, ',')) {
                                // Assume period as thousands separator, replace comma with period for decimal
                                $sanitized = str_replace(['.', ','], ['', '.'], $sanitized);
                            } else {
                                // Assume comma as thousands separator
                                $sanitized = str_replace(',', '', $sanitized);
                            }
                        } else {
                            // Treat comma as a decimal separator if present
                            $sanitized = str_replace(',', '.', $sanitized);
                        }

                        // Convert sanitized string to a float and apply the sign.
                        $floatValue = (float)$sanitized * $sign;
                    }

                    // Assign the parsed float value to the record's amount property.
                    $record->amount = $floatValue;
                }),
            ImportColumn::make('destination')
                ->label(__('bank_account_transaction.columns.destination'))
                ->rules(['max:255']),
            ImportColumn::make('category_id')
                ->label(__('bank_account_transaction.columns.category'))
                ->fillRecordUsing(function (BankAccountTransaction $record, string $state, $data): void {
                    $group = array_key_exists('group', $data) ? match ($data['group']) {
                        __('resources.transaction_categories.groups.fix_expense') => 'fix_expense',
                        __('resources.transaction_categories.groups.var_expense') => 'var_expense',
                        __('resources.transaction_categories.groups.fix_revenues') => 'fix_revenues',
                        __('resources.transaction_categories.groups.var_revenues') => 'var_revenues',
                        __('resources.transaction_categories.groups.transfers') => 'transfers',
                        default => ''
                    } : '';

                    $query = TransactionCategory::whereName($state);

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
                ->label(__('bank_account_transaction.columns.group'))
                ->fillRecordUsing(function (BankAccountTransaction $record, string $state): void {
                }),
            ImportColumn::make('notes')
                ->label(__('bank_account_transaction.columns.notes'))
                ->rules(['max:255'])];
    }

    public function resolveRecord(): ?BankAccountTransaction
    {
        return new BankAccountTransaction();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('bank_account.notifications.import.body_heading') . "\n\r" .
            __('bank_account.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('bank_account.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'bank-account-transaction-import';
    }
}
