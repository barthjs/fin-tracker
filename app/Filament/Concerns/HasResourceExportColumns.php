<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\Currency;
use Filament\Actions\Exports\ExportColumn;

trait HasResourceExportColumns
{
    public static function nameColumn(?string $name = 'name'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(__('fields.name'));
    }

    public static function currencyColumn(?string $name = 'currency'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(__('fields.currency'))
            ->formatStateUsing(fn (Currency $state): string => $state->getLabel());
    }

    public static function descriptionColumn(?string $name = 'description'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(__('fields.description'));
    }

    public static function colorColumn(?string $name = 'color'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(__('fields.color'));
    }

    public static function statusColumn(?string $name = 'is_active'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(__('fields.status'))
            ->enabledByDefault(false);
    }
}
