<?php declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Security;
use App\Tools\Convertor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SecurityImporter extends Importer
{
    protected static ?string $model = Security::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label(__('security.columns.name'))
                ->exampleHeader(__('security.columns.name'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('isin')
                ->label(__('security.columns.isin'))
                ->exampleHeader(__('security.columns.isin'))
                ->rules(['max:255']),
            ImportColumn::make('symbol')
                ->label(__('security.columns.symbol'))
                ->exampleHeader(__('security.columns.symbol'))
                ->rules(['max:255']),
            ImportColumn::make('price')
                ->label(__('security.columns.price'))
                ->exampleHeader(__('security.columns.price'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn(Security $record, string $state) => $record->price = Convertor::formatNumber($state)),
            ImportColumn::make('type')
                ->label(__('security.columns.type'))
                ->exampleHeader(__('security.columns.type'))
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(function (Security $record, string $state): void {
                    $record->type = match ($state) {
                        __('security.types.BOND') => 'BOND',
                        __('security.types.DERIVATIVE') => 'DERIVATIVE',
                        __('security.types.ETF') => 'ETF',
                        __('security.types.FUND') => 'FUND',
                        __('security.types.STOCK') => 'STOCK',
                        default => ''
                    };
                }),
            ImportColumn::make('description')
                ->label(__('security.columns.description'))
                ->exampleHeader(__('security.columns.description'))
                ->rules(['max:1000']),
            ImportColumn::make('color')
                ->label(__('widget.color'))
                ->exampleHeader(__('widget.color'))
                ->examples(function (): array {
                    $colors = [];
                    for ($i = 1; $i <= 3; $i++) {
                        $colors[] = strtolower(sprintf("#%06X", mt_rand(0, 0xFFFFFF)));
                    }
                    return $colors;
                })
                ->rules(['regex:/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/']),
            ImportColumn::make('active')
                ->label(__('table.active'))
                ->exampleHeader(__('table.active'))
                ->examples([1, 1, 1])
                ->boolean(),
        ];
    }

    public function resolveRecord(): Security
    {
        return Security::firstOrNew([
            'name' => trim($this->data['name']),
            'isin' => trim($this->data['isin']),
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('security.notifications.import.body_heading') . "\n\r" .
            __('security.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('security.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'security-import';
    }
}
