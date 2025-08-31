<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Enums\SecurityType;
use App\Filament\Concerns\HasResourceExportColumns;
use App\Models\Security;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

final class SecurityExporter extends Exporter
{
    use HasResourceExportColumns;

    protected static ?string $model = Security::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn(),

            ExportColumn::make('isin')
                ->label(__('security.fields.isin')),

            ExportColumn::make('symbol')
                ->label(__('security.fields.symbol')),

            self::typeColum()
                ->formatStateUsing(fn (SecurityType $state): string => $state->getLabel()),

            self::numericColumn('price', 6)
                ->label(__('fields.price')),

            self::numericColumn('total_quantity', 6)
                ->label(__('security.fields.total_quantity')),

            ExportColumn::make('description')
                ->label(__('fields.description')),

            self::colorColumn(),
            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('security.export.body_heading')."\n\r".
            __('security.export.body_success').number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r".__('security.export.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        return __('security.export.file_name').Carbon::now()->format('Y-m-d-H-i');
    }

    public function getJobBatchName(): string
    {
        return 'security-export';
    }
}
