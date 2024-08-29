<?php

namespace App\Filament\Exports;

use App\Models\BankAccountTransaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BankAccountTransactionExporter extends Exporter
{
    protected static ?string $model = BankAccountTransaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('date_time'),
            ExportColumn::make('bankAccount.name'),
            ExportColumn::make('amount')
                ->formatStateUsing(fn($state) => number_format($state, 2)),
            ExportColumn::make('currency')
                ->state(fn($record) => $record->bankAccount->currency->name),
            ExportColumn::make('destination'),
            ExportColumn::make('transactionCategory.name')
                ->label(__('resources.transaction_categories.table.name')),
            ExportColumn::make('transactionCategory.group')
                ->label(__('resources.transaction_categories.table.group'))
                ->formatStateUsing(fn($state) => __('resources.transaction_categories.groups')[$state->name]),
            ExportColumn::make('transactionCategory.type')
                ->label(__('resources.transaction_categories.table.type'))
                ->formatStateUsing(fn($state) => __('resources.transaction_categories.types')[$state->name]),
            ExportColumn::make('notes'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your bank account transaction export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'bank-account-transaction-export';
    }
}
