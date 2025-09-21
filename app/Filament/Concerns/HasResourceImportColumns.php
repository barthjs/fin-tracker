<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\Currency;
use App\Tools\Convertor;
use Carbon\CarbonImmutable;
use Exception;
use Filament\Actions\Imports\ImportColumn;

trait HasResourceImportColumns
{
    public static function nameColumn(?string $name = 'name'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.name'))
            ->exampleHeader(__('fields.name'))
            ->requiredMapping()
            ->rules(['required', 'max:255']);
    }

    public static function descriptionColumn(?string $name = 'description'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.description'))
            ->exampleHeader(__('fields.description'))
            ->examples(__('account.import.examples.description'))
            ->rules(['max:1000']);
    }

    public static function currencyColumn(?string $name = 'currency'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.currency'))
            ->exampleHeader(__('fields.currency'))
            ->examples(__('account.import.examples.currency'))
            ->requiredMapping()
            ->rules(['required'])
            ->castStateUsing(fn (?string $state): Currency => Currency::tryFrom(Currency::getCurrency($state)) ?? Currency::EUR);
    }

    public static function colorColumn(?string $name = 'color'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.color'))
            ->exampleHeader(__('fields.color'))
            ->examples(function (): array {
                $colors = [];
                for ($i = 1; $i <= 3; $i++) {
                    $colors[] = mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF)));
                }

                return $colors;
            })
            ->rules(['regex:/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/']);
    }

    public static function statusColumn(?string $name = 'is_active'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.status'))
            ->exampleHeader(__('fields.status'))
            ->examples([1, 1, 1])
            ->castStateUsing(fn (?string $state): bool => (bool) $state);
    }

    public static function dateTimeColumn(?string $name = 'date_time'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.date_time'))
            ->requiredMapping()
            ->rules(['required'])
            ->castStateUsing(function (string $state): CarbonImmutable {
                try {
                    $carbon = CarbonImmutable::parse($state);
                } catch (Exception) {
                    $carbon = CarbonImmutable::now();
                }

                return $carbon;
            });
    }

    public static function typeColumn(?string $name = 'type'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.type'))
            ->requiredMapping()
            ->rules(['required']);
    }

    public static function numericColumn(string $name): ImportColumn
    {
        return ImportColumn::make($name)
            ->requiredMapping()
            ->rules(['required'])
            ->castStateUsing(fn (string $state): float => abs(Convertor::formatNumber($state)));
    }

    public static function notesColumn(?string $name = 'notes'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.notes'))
            ->rules(['max:255']);
    }
}
