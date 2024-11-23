<?php declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use App\Tools\Convertor;
use Exception;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;

class TradeImporter extends Importer
{
    protected static ?string $model = Trade::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('date_time')
                ->label(__('trade.columns.date'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    try {
                        $carbon = Carbon::parse($state);
                    } catch (Exception) {
                        $carbon = Carbon::now();
                    }
                    $record->date_time = $carbon;
                }),
            ImportColumn::make('quantity')
                ->label(__('trade.columns.quantity'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn(Trade $record, string $state) => $record->quantity = Convertor::formatNumber($state)),
            ImportColumn::make('price')
                ->label(__('trade.columns.price'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn(Trade $record, string $state) => $record->price = Convertor::formatNumber($state)),
            ImportColumn::make('tax')
                ->label(__('trade.columns.tax'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn(Trade $record, string $state) => $record->tax = Convertor::formatNumber($state)),
            ImportColumn::make('fee')
                ->label(__('trade.columns.fee'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn(Trade $record, string $state) => $record->fee = Convertor::formatNumber($state)),
            ImportColumn::make('type')
                ->label(__('trade.columns.type'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    $record->type = match ($state) {
                        __('trade.types.BUY') => 'BUY',
                        __('trade.types.SELL') => 'SELL',
                        default => 'BUY',
                    };
                }),
            ImportColumn::make('account_id')
                ->label(__('trade.columns.account'))
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    $account = Account::whereName($state);
                    if ($account->count() > 1) {
                        $record->account_id = null;
                    } else {
                        $record->account_id = $account->first()->id ?? null;
                    }
                }),
            ImportColumn::make('portfolio')
                ->label(__('trade.columns.portfolio'))
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    $portfolio = Portfolio::whereName($state);
                    if ($portfolio->count() > 1) {
                        $record->portfolio_id = null;
                    } else {
                        $record->portfolio_id = $portfolio->first()->id ?? null;
                    }
                }),
            ImportColumn::make('isin')
                ->label(__('security.columns.isin'))
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    $security = Security::whereIsin($state);
                    if ($security->count() > 1) {
                        $record->security_id = null;
                    } else {
                        $record->security_id = $security->first()->id ?? null;
                    }
                }),
            ImportColumn::make('notes')
                ->label(__('trade.columns.notes'))
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?Trade
    {
        return new Trade();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('trade.notifications.import.body_heading') . "\n\r" .
            __('trade.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('trade.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'trade-import';
    }
}
