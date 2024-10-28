<?php

namespace App\Filament\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('date_time')
                ->label(__('bank_account_transaction.columns.date')),
            ExportColumn::make('account.name')
                ->label(__('bank_account_transaction.columns.account')),
            ExportColumn::make('amount')
                ->label(__('bank_account_transaction.columns.amount'))
                ->formatStateUsing(fn($state) => Number::format($state, 2, 4)),
            ExportColumn::make('currency')
                ->label(__('bank_account.columns.currency'))
                ->enabledByDefault(false)
                ->state(fn($record) => $record->account->currency->name),
            ExportColumn::make('destination')
                ->label(__('bank_account_transaction.columns.destination')),
            ExportColumn::make('category.name')
                ->label(__('bank_account_transaction.columns.category')),
            ExportColumn::make('category.group')
                ->label(__('bank_account_transaction.columns.group'))
                ->formatStateUsing(fn($state) => __('transaction_category.groups')[$state->name]),
            ExportColumn::make('category.type')
                ->label(__('bank_account_transaction.columns.type'))
                ->formatStateUsing(fn($state) => __('transaction_category.types')[$state->name]),
            ExportColumn::make('notes')
                ->label(__('bank_account_transaction.columns.notes')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('bank_account_transaction.notifications.export.body_heading') . "\n\r" .
            __('bank_account_transaction.notifications.export.body_success') . number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r" . __('bank_account.notifications.export.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'transaction-export';
    }

    public function getFileName(Export $export): string
    {
        return __('bank_account_transaction.notifications.export.file_name') . Carbon::now()->format('Y-m-d-h-i') . "_{$export->getKey()}";
    }
}
