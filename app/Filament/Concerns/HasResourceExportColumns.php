<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\Currency;
use Carbon\CarbonImmutable;
use Filament\Actions\Exports\ExportColumn;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

trait HasResourceExportColumns
{
    public static function nameColumn(?string $name = 'name'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(__('fields.name'));
    }

    public static function numericColumn(string $name, ?int $places = 2): ExportColumn
    {
        return ExportColumn::make($name)
            ->formatStateUsing(fn (float $state): ?string => Number::format($state, $places) ?: null);
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

    public static function dateTimeColum(?string $name = 'date_time'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(__('fields.date_time'))
            ->formatStateUsing(fn (CarbonImmutable $state): string => $state->format('Y-m-d, H:i'));
    }

    public static function typeColum(?string $name = 'type'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(__('fields.type'));
    }

    public static function accountColumn(?string $name = 'account.name'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(Str::ucfirst(__('account.label')));
    }

    public static function notesColumn(?string $name = 'notes'): ExportColumn
    {
        return ExportColumn::make($name)
            ->label(__('fields.notes'));
    }
}
