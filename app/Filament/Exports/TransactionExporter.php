<?php declare(strict_types=1);

namespace App\Filament\Exports;

use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('date_time')
                ->label(__('transaction.columns.date')),
            ExportColumn::make('amount')
                ->label(__('transaction.columns.amount'))
                ->formatStateUsing(fn($state): string => Number::format($state, 2)),
            ExportColumn::make('destination')
                ->label(__('transaction.columns.destination')),
            ExportColumn::make('account.name')
                ->label(__('transaction.columns.account')),
            ExportColumn::make('category.name')
                ->label(__('transaction.columns.category')),
            ExportColumn::make('category.group')
                ->label(__('transaction.columns.group'))
                ->formatStateUsing(fn(TransactionGroup $state): string => __('category.groups')[$state->name]),
            ExportColumn::make('category.type')
                ->label(__('transaction.columns.type'))
                ->formatStateUsing(fn(TransactionType $state): string => __('category.types')[$state->name]),
            ExportColumn::make('notes')
                ->label(__('transaction.columns.notes')),
            ExportColumn::make('currency')
                ->label(__('account.columns.currency'))
                ->enabledByDefault(false)
                ->state(fn(Transaction $record): string => $record->account->currency->name),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('transaction.notifications.export.body_heading') . "\n\r" .
            __('transaction.notifications.export.body_success') . number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r" . __('transaction.notifications.export.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'transaction-export';
    }

    public function getFileName(Export $export): string
    {
        return __('transaction.notifications.export.file_name') . Carbon::now()->format('Y-m-d-H-i');
    }
}
