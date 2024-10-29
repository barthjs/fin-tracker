<?php

namespace App\Filament\Imports;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
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
                        $carbon = Carbon::parse($state)->toDateTimeString();
                    } catch (Exception) {
                        $carbon = Carbon::now()->toDateTimeString();
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
                ->fillRecordUsing(function (Transaction $record, string $state): void {
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
