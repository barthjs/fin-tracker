<?php

namespace App\Filament\Exports;

use App\Models\BankAccount;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BankAccountExporter extends Exporter
{
    protected static ?string $model = BankAccount::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('balance')
                ->formatStateUsing(fn($state) => number_format($state, 2)),
            ExportColumn::make('currency')
                ->formatStateUsing(fn($state) => $state->name),
            ExportColumn::make('description'),
            ExportColumn::make('created_at')
                ->enabledByDefault(false),
            ExportColumn::make('updated_at')
                ->enabledByDefault(false),
            ExportColumn::make('active')
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your bank account export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'bank-account-export';
    }
}
