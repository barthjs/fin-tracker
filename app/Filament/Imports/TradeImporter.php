<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Actions\GetOrCreateDefaultAccount;
use App\Actions\GetOrCreateDefaultPortfolio;
use App\Actions\GetOrCreateDefaultSecurity;
use App\Enums\TradeType;
use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use App\Services\TradeService;
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
                        $record->account_id = resolve(GetOrCreateDefaultAccount::class)()->id;
                    } else {
                        $record->account_id = $account->first()->id ?? resolve(GetOrCreateDefaultAccount::class)()->id;
                    }
                }),

            ImportColumn::make('portfolio')
                ->label(__('portfolio.label'))
                ->fillRecordUsing(function (Trade $record, ?string $state): void {
                    $portfolio = Portfolio::whereName($state);
                    if ($portfolio->count() > 1) {
                        $record->portfolio_id = resolve(GetOrCreateDefaultPortfolio::class)()->id;
                    } else {
                        $record->portfolio_id = $portfolio->first()->id ?? resolve(GetOrCreateDefaultPortfolio::class)()->id;
                    }
                }),

            ImportColumn::make('isin')
                ->label(__('security.fields.isin'))
                ->fillRecordUsing(function (Trade $record, ?string $state): void {
                    $security = Security::whereIsin($state);
                    if ($security->count() > 1) {
                        $record->security_id = resolve(GetOrCreateDefaultSecurity::class)()->id;
                    } else {
                        $record->security_id = $security->first()->id ?? resolve(GetOrCreateDefaultSecurity::class)()->id;
                    }
                }),

            self::notesColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('trade.import.body_heading')."\n\r".
            __('trade.import.body_success').number_format($import->successful_rows);

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= "\n\r".__('trade.import.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function resolveRecord(): Trade
    {
        return new Trade;
    }

    public function saveRecord(): void
    {
        $service = resolve(TradeService::class);
        /** @phpstan-ignore-next-line  */
        $this->record = $service->create($this->record->toArray());
    }

    /**
     * @codeCoverageIgnore
     */
    public function getJobBatchName(): string
    {
        return 'trade-import';
    }
}
