<?php

namespace App\Filament\Exports;

use App\Models\TransactionCategory;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TransactionCategoryExporter extends Exporter
{
    protected static ?string $model = TransactionCategory::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name'),
            ExportColumn::make('group')
                ->formatStateUsing(fn($state) => $state->name),
            ExportColumn::make('type')
                ->formatStateUsing(fn($state) => $state->name),
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
        $body = 'Your transaction category export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'transaction-category-export';
    }
}
