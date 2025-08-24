<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\Currency;
use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Portfolio;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

final class PortfolioImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Portfolio::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn()
                ->examples(__('account.import.examples.name')),

            self::currencyColumn()
                ->examples(__('account.import.examples.currency'))
                ->fillRecordUsing(function (Portfolio $record, string $state): void {
                    $record->currency = Currency::getCurrency($state);
                }),

            self::descriptionColumn()
                ->examples(__('account.import.examples.description')),

            self::colorColumn(),
            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('portfolio.import.body_heading')."\n\r".
            __('portfolio.import.body_success').number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r".__('portfolio.import.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function resolveRecord(): ?Portfolio
    {
        return Portfolio::firstOrNew([
            'name' => mb_trim($this->data['name']),
            'user_id' => auth()->user()->id,
        ]);
    }

    public function getJobBatchName(): ?string
    {
        return 'portfolio-import';
    }
}
