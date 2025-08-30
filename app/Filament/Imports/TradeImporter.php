<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\TradeType;
use App\Filament\Concerns\HasResourceImportColumns;
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

final class TradeImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Trade::class;

    public static function getColumns(): array
    {
        return [
            self::dateTimeColumn()
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    try {
                        $carbon = Carbon::parse($state);
                    } catch (Exception) {
                        $carbon = Carbon::now();
                    }
                    $record->date_time = $carbon;
                }),

            self::typeColumn()
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    $record->type = match ($state) {
                        TradeType::Buy->getLabel() => TradeType::Buy,
                        TradeType::Sell->getLabel() => TradeType::Sell,
                        default => TradeType::Buy,
                    };
                }),

            self::numericColumn('quantity')
                ->label(__('trade.fields.quantity'))
                ->fillRecordUsing(fn (Trade $record, string $state) => $record->quantity = abs(Convertor::formatNumber($state))),

            self::numericColumn('price')
                ->label(__('fields.price'))
                ->fillRecordUsing(fn (Trade $record, string $state) => $record->price = abs(Convertor::formatNumber($state))),

            self::numericColumn('tax')
                ->label(__('trade.fields.tax'))
                ->fillRecordUsing(fn (Trade $record, string $state) => $record->tax = abs(Convertor::formatNumber($state))),

            self::numericColumn('fee')
                ->label(__('trade.fields.fee'))
                ->fillRecordUsing(fn (Trade $record, string $state) => $record->fee = abs(Convertor::formatNumber($state))),

            ImportColumn::make('account_id')
                ->label(__('trade.columns.account'))
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    $account = Account::whereName($state);
                    if ($account->count() > 1) {
                        $record->account_id = Account::getOrCreateDefaultAccount()->id;
                    } else {
                        $record->account_id = $account->first()->id ?? Account::getOrCreateDefaultAccount()->id;
                    }
                }),

            ImportColumn::make('portfolio')
                ->label(__('trade.columns.portfolio'))
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    $portfolio = Portfolio::whereName($state);
                    if ($portfolio->count() > 1) {
                        $record->portfolio_id = Portfolio::getOrCreateDefaultPortfolio()->id;
                    } else {
                        $record->portfolio_id = $portfolio->first()->id ?? Portfolio::getOrCreateDefaultPortfolio()->id;
                    }
                }),

            ImportColumn::make('isin')
                ->label(__('security.columns.isin'))
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    $security = Security::whereIsin($state);
                    if ($security->count() > 1) {
                        $record->security_id = Security::getOrCreateDefaultSecurity()->id;
                    } else {
                        $record->security_id = $security->first()->id ?? Security::getOrCreateDefaultSecurity()->id;
                    }
                }),

            self::notesColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('trade.import.body_heading')."\n\r".
            __('trade.import.body_success').number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r".__('trade.import.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function resolveRecord(): Trade
    {
        return new Trade;
    }

    public function getJobBatchName(): string
    {
        return 'trade-import';
    }
}
