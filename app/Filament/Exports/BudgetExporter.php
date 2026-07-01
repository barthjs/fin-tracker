<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Concerns\HasResourceExportColumns;
use App\Models\Budget;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Facades\Date;

final class BudgetExporter extends Exporter
{
    use HasResourceExportColumns;

    protected static ?string $model = Budget::class;

    public static function getColumns(): array
    {
        return [
            self::numericColumn('amount')
                ->label(__('budget.fields.amount')),

            self::categoryColumn(),

            ExportColumn::make('period_unit')
                ->label(__('budget.fields.period_unit')),

            ExportColumn::make('period_frequency')
                ->label(__('budget.fields.period_frequency')),

            self::dateColumn('starts_at')
                ->label(__('budget.fields.starts_at')),

            self::dateColumn('ends_at')
                ->label(__('budget.fields.ends_at')),

            ExportColumn::make('warning_threshold')
                ->label(__('budget.fields.warning_threshold')),

            ExportColumn::make('notify_on_exceed')
                ->label(__('budget.fields.notify_on_exceed')),

            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('budget.export.body_heading')."\n\r".
            __('budget.export.body_success').number_format($export->successful_rows);

        if (($failedRowsCount = $export->getFailedRowsCount()) !== 0) {
            $body .= "\n\r".__('budget.export.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        return __('budget.export.file_name').Date::now()->timezone(auth()->user()->timezone)->format('Y-m-d-H-i');
    }

    /**
     * @codeCoverageIgnore
     */
    public function getJobBatchName(): string
    {
        return 'budget-export';
    }
}
