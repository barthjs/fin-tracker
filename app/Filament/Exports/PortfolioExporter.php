<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Concerns\HasResourceExportColumns;
use App\Models\Portfolio;
use Carbon\Carbon;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

final class PortfolioExporter extends Exporter
{
    use HasResourceExportColumns;

    protected static ?string $model = Portfolio::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn(),

            self::numericColumn('market_value')
                ->label(__('fields.market_value')),

            self::currencyColumn(),
            self::descriptionColumn(),
            self::colorColumn(),
            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('portfolio.export.body_heading')."\n\r".
            __('portfolio.export.body_success').number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r".__('portfolio.export.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        return __('portfolio.export.file_name').Carbon::now()->format('Y-m-d-H-i');
    }

    public function getJobBatchName(): string
    {
        return 'portfolio-export';
    }
}
