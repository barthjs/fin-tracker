<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Filament\Concerns\HasResourceExportColumns;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

final class TransactionExporter extends Exporter
{
    use HasResourceExportColumns;

    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            self::dateTimeColum(),

            self::typeColum()
                ->formatStateUsing(fn (TransactionType $state): string => $state->getLabel()),

            ExportColumn::make('amount')
                ->label(__('transaction.fields.amount'))
                ->formatStateUsing(fn (float $state): string|false => Number::format($state, 2)),

            ExportColumn::make('payee')
                ->label(__('transaction.fields.payee')),

            self::accountColumn(),

            ExportColumn::make('category.name')
                ->label(__('category.label')),

            ExportColumn::make('category.group')
                ->label(__('category.fields.group'))
                ->formatStateUsing(fn (CategoryGroup $state): string => $state->getLabel()),

            self::notesColumn(),

            ExportColumn::make('currency')
                ->label(__('fields.currency'))
                ->enabledByDefault(false)
                ->state(fn (Transaction $record): string => $record->account->currency->getLabel()),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('transaction.export.body_heading')."\n\r".
            __('transaction.export.body_success').number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r".__('transaction.export.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        return __('transaction.export.file_name').Carbon::now()->format('Y-m-d-H-i');
    }

    public function getJobBatchName(): string
    {
        return 'transaction-export';
    }
}
