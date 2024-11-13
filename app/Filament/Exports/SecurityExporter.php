<?php

namespace App\Filament\Exports;

use App\Models\Security;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class SecurityExporter extends Exporter
{
    protected static ?string $model = Security::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label(__('security.columns.name')),
            ExportColumn::make('isin')
                ->label(__('security.columns.isin')),
            ExportColumn::make('symbol')
                ->label(__('security.columns.symbol')),
            ExportColumn::make('price')
                ->label(__('security.columns.price'))
                ->formatStateUsing(fn($state): string => Number::format($state)),
            ExportColumn::make('total_quantity')
                ->label(__('security.columns.total_quantity'))
                ->formatStateUsing(fn($state): string => Number::format($state)),
            ExportColumn::make('description')
                ->label(__('security.columns.description')),
            ExportColumn::make('type')
                ->label(__('security.columns.type'))
                ->formatStateUsing(fn($state): string => __('security.types')[$state->name]),
            ExportColumn::make('color')
                ->label(__('widget.color')),
            ExportColumn::make('active')
                ->label(__('table.active'))
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('security.notifications.export.body_heading') . "\n\r" .
            __('security.notifications.export.body_success') . number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r" . __('security.notifications.export.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'security-export';
    }

    public function getFileName(Export $export): string
    {
        return __('security.notifications.export.file_name') . Carbon::now()->format('Y-m-d-h-i') . "_{$export->getKey()}";
    }
}
