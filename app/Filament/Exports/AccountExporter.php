<?php

namespace App\Filament\Exports;

use App\Models\Account;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class AccountExporter extends Exporter
{
    protected static ?string $model = Account::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label(__('account.columns.name')),
            ExportColumn::make('balance')
                ->label(__('account.columns.balance'))
                ->formatStateUsing(fn($state): string => Number::format($state)),
            ExportColumn::make('currency')
                ->label(__('account.columns.currency'))
                ->formatStateUsing(fn($state): string => $state->name),
            ExportColumn::make('description')
                ->label(__('account.columns.description')),
            ExportColumn::make('color')
                ->label(__('widget.color')),
            ExportColumn::make('active')
                ->label(__('table.active'))
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('account.notifications.export.body_heading') . "\n\r" .
            __('account.notifications.export.body_success') . number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r" . __('account.notifications.export.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'account-export';
    }

    public function getFileName(Export $export): string
    {
        return __('account.notifications.export.file_name') . Carbon::now()->format('Y-m-d-h-i') . "_{$export->getKey()}";
    }
}
