<?php

namespace App\Filament\Exports;

use App\Models\Portfolio;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PortfolioExporter extends Exporter
{
    protected static ?string $model = Portfolio::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label(__('portfolio.columns.name')),
            ExportColumn::make('market_value')
                ->label(__('portfolio.columns.market_value'))
                ->formatStateUsing(fn($state): string => Number::format($state)),
            ExportColumn::make('description')
                ->label(__('portfolio.columns.description')),
            ExportColumn::make('color')
                ->label(__('widget.color')),
            ExportColumn::make('active')
                ->label(__('table.active'))
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('portfolio.notifications.export.body_heading') . "\n\r" .
            __('portfolio.notifications.export.body_success') . number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r" . __('portfolio.notifications.export.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'portfolio-export';
    }

    public function getFileName(Export $export): string
    {
        return __('portfolio.notifications.export.file_name') . Carbon::now()->format('Y-m-d-h-i') . "_{$export->getKey()}";
    }
}
