<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Concerns\HasResourceExportColumns;
use App\Models\Portfolio;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Date;

final class PortfolioExporter extends Exporter
{
    use HasResourceExportColumns;

    protected static ?string $model = Portfolio::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn(),

            self::numericColumn('market_value', 6)
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

        if (($failedRowsCount = $export->getFailedRowsCount()) !== 0) {
            $body .= "\n\r".__('portfolio.export.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        return __('portfolio.export.file_name').Date::now().Date::now()->timezone(auth()->user()->timezone)->format('Y-m-d-H-i');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getJobBatchName(): string
    {
        return 'portfolio-export';
    }
}
