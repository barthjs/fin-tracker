<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\TradeType;
use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

final class TradeImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Trade::class;

    public static function getColumns(): array
    {
        return [
            self::dateTimeColumn(),

            self::typeColumn()
                ->fillRecordUsing(function (Trade $record, string $state): void {
                    $record->type = match ($state) {
                        TradeType::Buy->getLabel() => TradeType::Buy,
                        TradeType::Sell->getLabel() => TradeType::Sell,
                        default => TradeType::Buy,
                    };
                }),

            self::numericColumn('quantity')
                ->label(__('trade.fields.quantity')),

            self::numericColumn('price')
                ->label(__('fields.price')),

            self::numericColumn('tax')
                ->label(__('trade.fields.tax')),

            self::numericColumn('fee')
                ->label(__('trade.fields.fee')),

            ImportColumn::make('account_id')
                ->label(__('account.label'))
                ->fillRecordUsing(function (Trade $record, ?string $state): void {
                    $account = Account::whereName($state);
                    if ($account->count() > 1) {
                        $record->account_id = Account::getOrCreateDefaultAccount()->id;
                    } else {
                        $record->account_id = $account->first()->id ?? Account::getOrCreateDefaultAccount()->id;
                    }
                }),

            ImportColumn::make('portfolio')
                ->label(__('portfolio.label'))
                ->fillRecordUsing(function (Trade $record, ?string $state): void {
                    $portfolio = Portfolio::whereName($state);
                    if ($portfolio->count() > 1) {
                        $record->portfolio_id = Portfolio::getOrCreateDefaultPortfolio()->id;
                    } else {
                        $record->portfolio_id = $portfolio->first()->id ?? Portfolio::getOrCreateDefaultPortfolio()->id;
                    }
                }),

            ImportColumn::make('isin')
                ->label(__('security.fields.isin'))
                ->fillRecordUsing(function (Trade $record, ?string $state): void {
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
