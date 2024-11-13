<?php

namespace App\Filament\Exports;

use App\Models\Trade;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TradeExporter extends Exporter
{
    protected static ?string $model = Trade::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('date_time')
                ->label(__('trade.columns.date')),
            ExportColumn::make('total_amount')
                ->label(__('trade.columns.total_amount'))
                ->formatStateUsing(fn($state): string => Number::format($state)),
            ExportColumn::make('quantity')
                ->label(__('trade.columns.quantity'))
                ->formatStateUsing(fn($state): string => Number::format($state)),
            ExportColumn::make('price')
                ->label(__('trade.columns.price'))
                ->formatStateUsing(fn($state): string => Number::format($state)),
            ExportColumn::make('tax')
                ->label(__('trade.columns.tax'))
                ->formatStateUsing(fn($state): string => Number::format($state)),
            ExportColumn::make('fee')
                ->label(__('trade.columns.fee'))
                ->formatStateUsing(fn($state): string => Number::format($state)),
            ExportColumn::make('account.name')
                ->label(__('trade.columns.account')),
            ExportColumn::make('portfolio.name')
                ->label(__('trade.columns.portfolio')),
            ExportColumn::make('security.name')
                ->label(__('trade.columns.security')),
            ExportColumn::make('notes')
                ->label(__('trade.columns.notes')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('trade.notifications.export.body_heading') . "\n\r" .
            __('trade.notifications.export.body_success') . number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r" . __('trade.notifications.export.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'trade-export';
    }

    public function getFileName(Export $export): string
    {
        return __('trade.notifications.export.file_name') . Carbon::now()->format('Y-m-d-h-i') . "_{$export->getKey()}";
    }
}
