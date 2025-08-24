<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\SecurityType;
use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Security;
use App\Tools\Convertor;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

final class SecurityImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Security::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label(__('fields.name'))
                ->exampleHeader(__('fields.name'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('isin')
                ->label(__('security.fields.isin'))
                ->exampleHeader(__('security.fields.isin'))
                ->rules(['max:255']),

            ImportColumn::make('type')
                ->label(__('fields.type'))
                ->exampleHeader(__('fields.type'))
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(function (Security $record, string $state): void {
                    $record->type = match ($state) {
                        SecurityType::Bond->getLabel() => SecurityType::Bond,
                        SecurityType::Derivative->getLabel() => SecurityType::Derivative,
                        SecurityType::ETF->getLabel() => SecurityType::ETF,
                        SecurityType::Fund->getLabel() => SecurityType::Fund,
                        SecurityType::Stock->getLabel() => SecurityType::Stock,
                        default => SecurityType::Stock,
                    };
                }),

            ImportColumn::make('symbol')
                ->label(__('security.fields.symbol'))
                ->exampleHeader(__('security.fields.symbol'))
                ->rules(['max:255']),

            ImportColumn::make('price')
                ->label(__('fields.price'))
                ->exampleHeader(__('fields.price'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(fn (Security $record, string $state) => $record->price = Convertor::formatNumber($state)),

            self::descriptionColumn(),
            self::colorColumn(),
            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('security.import.body_heading')."\n\r".
            __('security.import.body_success').number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r".__('security.import.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function resolveRecord(): Security
    {
        return Security::firstOrNew([
            'name' => mb_trim($this->data['name']),
            'isin' => mb_trim($this->data['isin']),
            'user_id' => auth()->user()->id,
        ]);
    }

    public function getJobBatchName(): ?string
    {
        return 'security-import';
    }
}
