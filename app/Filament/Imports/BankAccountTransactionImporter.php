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
                ->fillRecordUsing(function (BankAccountTransaction $record, string $state): void {
                    $record->bank_account_id = BankAccount::whereName($state)->first()->id ?? null;
                }),
            ImportColumn::make('amount')
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
                ->rules(['max:255']),
            ImportColumn::make('category_id')
                ->fillRecordUsing(function (BankAccountTransaction $record, string $state, $data): void {
                    $type = array_key_exists('type', $data) ? match ($data['type']) {
                        __('resources.transaction_categories.types.expense') => 'expense',
                        __('resources.transaction_categories.types.revenue') => 'revenue',
                        __('resources.transaction_categories.types.transfer') => 'transfer',
                        default => ''
                    } : '';

                    $group = array_key_exists('group', $data) ? match ($data['group']) {
                        __('resources.transaction_categories.groups.fix_expense') => 'fix_expense',
                        __('resources.transaction_categories.groups.var_expense') => 'var_expense',
                        __('resources.transaction_categories.groups.fix_revenues') => 'var_revenues',
                        __('resources.transaction_categories.groups.var_revenues') => 'fix_revenues',
                        __('resources.transaction_categories.groups.transfers') => 'transfers',
                        default => ''
                    } : '';

                    $query = TransactionCategory::whereName($state);

                    if ($type) {
                        $query->whereType($type);
                    }

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
            ImportColumn::make('type')
                ->fillRecordUsing(function (BankAccountTransaction $record, string $state): void {
                }),
            ImportColumn::make('group')
                ->fillRecordUsing(function (BankAccountTransaction $record, string $state): void {
                }),
            ImportColumn::make('notes')
                ->rules(['max:255']),];
    }

    public function resolveRecord(): ?BankAccountTransaction
    {
        return new BankAccountTransaction();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your bank account transaction import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
