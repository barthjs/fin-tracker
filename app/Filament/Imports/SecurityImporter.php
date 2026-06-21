<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\Country;
use App\Enums\SecurityRegion;
use App\Enums\SecuritySector;
use App\Enums\SecurityType;
use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Security;
use App\Tools\Convertor;
use BackedEnum;
use Closure;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
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
                ->castStateUsing(fn (string $state): SecurityType => match ($state) {
                    SecurityType::Bond->getLabel() => SecurityType::Bond,
                    SecurityType::Derivative->getLabel() => SecurityType::Derivative,
                    SecurityType::ETF->getLabel() => SecurityType::ETF,
                    SecurityType::Fund->getLabel() => SecurityType::Fund,
                    SecurityType::Stock->getLabel() => SecurityType::Stock,
                    default => SecurityType::Stock,
                }),

            ImportColumn::make('symbol')
                ->label(__('security.fields.symbol'))
                ->exampleHeader(__('security.fields.symbol'))
                ->examples(__('security.import.examples.symbol'))
                ->rules(['max:255']),

            ImportColumn::make('region')
                ->label(__('security.fields.region'))
                ->exampleHeader(__('security.fields.region'))
                ->examples(__('security.import.examples.region'))
                ->castStateUsing(fn (?string $state): ?SecurityRegion => self::castOptionalEnum($state, SecurityRegion::tryFromValueOrLabel(...))),

            ImportColumn::make('sector')
                ->label(__('security.fields.sector'))
                ->exampleHeader(__('security.fields.sector'))
                ->examples(__('security.import.examples.sector'))
                ->castStateUsing(fn (?string $state): ?SecuritySector => self::castOptionalEnum($state, SecuritySector::tryFromValueOrLabel(...))),

            ImportColumn::make('country')
                ->label(__('security.fields.country'))
                ->exampleHeader(__('security.fields.country'))
                ->examples(__('security.import.examples.country'))
                ->castStateUsing(fn (?string $state): ?Country => self::castOptionalEnum($state, Country::tryFromValueOrLabel(...))),

            ImportColumn::make('exchange')
                ->label(__('security.fields.exchange'))
                ->exampleHeader(__('security.fields.exchange'))
                ->examples(__('security.import.examples.exchange'))
                ->rules(['max:50']),

            ImportColumn::make('ter')
                ->label(__('security.fields.ter'))
                ->exampleHeader(__('security.fields.ter'))
                ->examples(__('security.import.examples.ter'))
                ->castStateUsing(fn (?string $state): ?float => $state === null || $state === '' ? null : abs(Convertor::formatNumber($state))),

            ImportColumn::make('website')
                ->label(__('security.fields.website'))
                ->exampleHeader(__('security.fields.website'))
                ->examples(__('security.import.examples.website'))
                ->rules(['nullable', 'url', 'max:255']),

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

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
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
            ->unless(empty($this->data['color']), function (Builder $query): void {
                $query->where('color', $this->data['color']);
            })
            ->first() ?? new Security([
                'name' => $this->data['name'],
                'type' => $this->data['type'],
                'color' => $this->data['color'] ?? mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => auth()->id(),
            ]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getJobBatchName(): string
    {
        return 'security-import';
    }

    /**
     * Resolves an optional enum column and fails the row when a non-empty value matches no case.
     *
     * @template TEnum of BackedEnum
     *
     * @param  Closure(string): (TEnum|null)  $resolve
     * @return TEnum|null
     */
    private static function castOptionalEnum(?string $state, Closure $resolve): ?BackedEnum
    {
        if ($state === null || mb_trim($state) === '') {
            return null;
        }

        return $resolve($state) ?? throw new RowImportFailedException(__('security.import.errors.invalid_value', ['value' => $state]));
    }
}
