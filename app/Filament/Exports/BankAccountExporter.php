<?php

namespace App\Filament\Exports;

use App\Models\BankAccount;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class BankAccountExporter extends Exporter
{
    protected static ?string $model = BankAccount::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label(__('bank_account.columns.name')),
            ExportColumn::make('balance')
                ->label(__('bank_account.columns.balance'))
                ->formatStateUsing(fn($state) => Number::format($state,2)),
            ExportColumn::make('currency')
                ->label(__('bank_account.columns.currency'))
                ->formatStateUsing(fn($state) => $state->name),
            ExportColumn::make('description')
                ->label(__('bank_account.columns.description')),
            ExportColumn::make('created_at')
                ->label(__('table.created_at'))
                ->enabledByDefault(false),
            ExportColumn::make('updated_at')
                ->label(__('table.updated_at'))
                ->enabledByDefault(false),
            ExportColumn::make('active')
                ->label(__('table.active'))
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('bank_account.notifications.export.body_heading') . "\n\r" .
            __('bank_account.notifications.export.body_success') . number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r" . __('bank_account.notifications.export.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'bank-account-export';
    }

    public function getFileName(Export $export): string
    {
        return __('bank_account.notifications.export.file_name') . Carbon::today()->format('Y-m-d') . "_{$export->getKey()}";
    }
}
