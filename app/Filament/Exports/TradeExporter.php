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
use Illuminate\Support\Str;

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

            self::numericColumn('total_amount', 6)
                ->label(__('trade.fields.total_amount')),

            self::numericColumn('quantity', 6)
                ->label(__('trade.fields.quantity')),

            self::numericColumn('price', 6)
                ->label(__('fields.price')),

            self::numericColumn('tax', 6)
                ->label(__('trade.fields.tax')),

            self::numericColumn('fee', 6)
                ->label(__('trade.fields.fee')),

            self::accountColumn(),

            ExportColumn::make('portfolio.name')
                ->label(Str::ucfirst(__('portfolio.label'))),

            ExportColumn::make('security.isin')
                ->label(__('security.fields.isin')),

            ExportColumn::make('security.name')
                ->label(Str::ucfirst(__('security.label'))),

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
