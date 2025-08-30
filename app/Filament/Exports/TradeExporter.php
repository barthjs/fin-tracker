<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Enums\TradeType;
use App\Filament\Concerns\HasResourceExportColumns;
use App\Models\Trade;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

final class TradeExporter extends Exporter
{
    use HasResourceExportColumns;

    protected static ?string $model = Trade::class;

    public static function getColumns(): array
    {
        return [
            self::dateTimeColum(),

            self::typeColum()
                ->formatStateUsing(fn (TradeType $state): string => $state->getLabel()),

            self::tradeAmountColum('total_amount')
                ->label(__('trade.fields.total_amount')),

            self::tradeAmountColum('quantity')
                ->label(__('trade.fields.quantity')),

            self::tradeAmountColum('price')
                ->label(__('fields.price')),

            self::tradeAmountColum('tax')
                ->label(__('trade.fields.tax')),

            self::tradeAmountColum('fee')
                ->label(__('trade.fields.fee')),

            self::accountColumn(),

            ExportColumn::make('portfolio.name')
                ->label(__('portfolio.label')),

            ExportColumn::make('security.isin')
                ->label(__('security.fields.isin')),

            ExportColumn::make('security.name')
                ->label(__('security.label')),

            self::notesColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('trade.export.body_heading')."\n\r".
            __('trade.export.body_success').number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r".__('trade.export.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        return __('trade.export.file_name').Carbon::now()->format('Y-m-d-H-i');
    }

    public function getJobBatchName(): string
    {
        return 'trade-export';
    }
}
