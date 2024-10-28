<?php

namespace App\Filament\Exports;

use App\Models\Category;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CategoryExporter extends Exporter
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label(__('transaction_category.columns.name')),
            ExportColumn::make('group')
                ->label(__('transaction_category.columns.group'))
                ->formatStateUsing(fn($state): string => __('transaction_category.groups')[$state->name]),
            ExportColumn::make('type')
                ->label(__('transaction_category.columns.type'))
                ->formatStateUsing(fn($state): string => __('transaction_category.types')[$state->name]),
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
        $body = __('transaction_category.notifications.export.body_heading') . "\n\r" .
            __('transaction_category.notifications.export.body_success') . number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r" . __('transaction_category.notifications.export.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'category-export';
    }

    public function getFileName(Export $export): string
    {
        return __('transaction_category.notifications.export.file_name') . Carbon::now()->format('Y-m-d-h-i') . "_{$export->getKey()}";
    }
}
