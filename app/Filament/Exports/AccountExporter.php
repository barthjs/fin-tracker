<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Concerns\HasResourceExportColumns;
use App\Models\Account;
use Carbon\Carbon;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

final class AccountExporter extends Exporter
{
    use HasResourceExportColumns;

    protected static ?string $model = Account::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn(),

            self::numericColumn('balance')
                ->label(__('account.fields.balance')),

            self::currencyColumn(),
            self::descriptionColumn(),
            self::colorColumn(),
            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('account.export.body_heading')."\n\r".
            __('account.export.body_success').number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r".__('account.export.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        return __('account.export.file_name').Carbon::now()->format('Y-m-d-H-i');
    }

    public function getJobBatchName(): string
    {
        return 'account-export';
    }
}
