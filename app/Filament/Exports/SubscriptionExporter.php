<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Concerns\HasResourceExportColumns;
use App\Models\Subscription;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

final class SubscriptionExporter extends Exporter
{
    use HasResourceExportColumns;

    protected static ?string $model = Subscription::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn(),

            self::numericColumn('amount')
                ->label(__('fields.amount')),

            ExportColumn::make('period_unit')
                ->label(__('subscription.fields.period_unit')),

            ExportColumn::make('period_frequency')
                ->label(__('subscription.fields.period_frequency')),

            ExportColumn::make('day_of_month')
                ->label(__('subscription.fields.day_of_month')),

            self::dateColumn('started_at')
                ->label(__('subscription.fields.started_at')),

            self::dateColumn('next_payment_date')
                ->label(__('subscription.fields.next_payment_date')),

            self::dateColumn('ended_at')
                ->label(__('subscription.fields.ended_at')),

            ExportColumn::make('auto_generate_transaction')
                ->label(__('subscription.fields.auto_generate_transaction')),

            ExportColumn::make('remind_before_payment')
                ->label(__('subscription.fields.remind_before_payment')),

            ExportColumn::make('reminder_days_before')
                ->label(__('subscription.fields.reminder_days_before')),

            self::accountColumn(),
            self::categoryColumn(),
            self::descriptionColumn(),
            self::colorColumn(),
            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('subscription.export.body_heading')."\n\r".
            __('subscription.export.body_success').number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r".__('subscription.export.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        return __('subscription.export.file_name').Carbon::now()->format('Y-m-d-H-i');
    }

    public function getJobBatchName(): string
    {
        return 'subscription-export';
    }
}
