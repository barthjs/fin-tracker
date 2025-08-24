<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

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
            ->rules(['max:1000']);
    }

    public static function currencyColumn(?string $name = 'currency'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.currency'))
            ->exampleHeader(__('fields.currency'))
            ->examples(['EUR', 'USD', 'GBP']);
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
            ->boolean();
    }

    public static function dateTimeColumn(?string $name = 'date_time'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.date_time'))
            ->requiredMapping()
            ->rules(['required']);
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
            ->rules(['required']);
    }

    public static function notesColumn(?string $name = 'notes'): ImportColumn
    {
        return ImportColumn::make($name)
            ->label(__('fields.notes'))
            ->rules(['max:255']);
    }
}
