<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Portfolio;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Builder;

final class PortfolioImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Portfolio::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn()
                ->examples(__('account.import.examples.name')),

            self::currencyColumn(),

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

    public function resolveRecord(): Portfolio
    {
        return Portfolio::query()
            ->where('user_id', auth()->id())
            ->where('name', $this->data['name'])
            ->where('currency', $this->data['currency'])
            ->when(! empty($this->data['color']), function (Builder $query): void {
                $query->where('color', $this->data['color']);
            })
            ->first() ?? new Portfolio([
                'name' => $this->data['name'],
                'currency' => $this->data['currency'],
                'color' => $this->data['color'] ?? mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => auth()->id(),
            ]);
    }

    public function getJobBatchName(): string
    {
        return 'portfolio-import';
    }
}
