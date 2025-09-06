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
use Illuminate\Database\Eloquent\Builder;

final class SecurityImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Security::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn()
                ->examples(__('security.import.examples.name')),

            ImportColumn::make('isin')
                ->label(__('security.fields.isin'))
                ->exampleHeader(__('security.fields.isin'))
                ->examples(__('security.import.examples.isin'))
                ->rules(['max:255']),

            ImportColumn::make('type')
                ->label(__('fields.type'))
                ->exampleHeader(__('fields.type'))
                ->examples(__('security.import.examples.type'))
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(function (string $state): SecurityType {
                    return match ($state) {
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
                ->examples(__('security.import.examples.symbol'))
                ->rules(['max:255']),

            ImportColumn::make('price')
                ->label(__('fields.price'))
                ->exampleHeader(__('fields.price'))
                ->examples(__('security.import.examples.price'))
                ->castStateUsing(fn (?string $state): float => abs(Convertor::formatNumber($state ?? ''))),

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
        return Security::query()
            ->where('user_id', auth()->id())
            ->where('name', $this->data['name'])
            ->where('type', $this->data['type'])
            ->when(! empty($this->data['color']), function (Builder $query): void {
                $query->where('color', $this->data['color']);
            })
            ->first() ?? new Security([
                'name' => $this->data['name'],
                'type' => $this->data['type'],
                'color' => $this->data['color'] ?? mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => auth()->id(),
            ]);
    }

    public function getJobBatchName(): string
    {
        return 'security-import';
    }
}
