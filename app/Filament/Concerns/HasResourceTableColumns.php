<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

trait HasResourceTableColumns
{
    public static function logoAndNameColumn(?string $name = 'name'): TextColumn
    {
        return TextColumn::make($name)
            ->view('filament.tables.columns.logo-and-name-column')
            ->label(__('fields.name'))
            ->searchable()
            ->sortable();
    }

    public static function nameColumn(string $name): TextColumn
    {
        return TextColumn::make($name)
            ->searchable()
            ->sortable();
    }

    public static function descriptionColumn(?string $name = 'description'): TextColumn
    {
        return TextColumn::make($name)
            ->label(__('fields.description'))
            ->wrap()
            ->toggleable();
    }

    public static function currencyColumn(?string $name = 'currency'): TextColumn
    {
        return TextColumn::make($name)
            ->label(__('fields.currency'))
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    public static function statusColumn(?string $name = 'is_active'): IconColumn
    {
        return IconColumn::make($name)
            ->label(__('fields.status'))
            ->tooltip(fn (bool $state): string => $state ? (string) __('fields.status_active') : (string) __('fields.status_inactive'))
            ->boolean()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    public static function createdAtColumn(?string $name = 'created_at'): TextColumn
    {
        return TextColumn::make($name)
            ->label(__('fields.created_at'))
            ->dateTime('Y-m-d, H:i:s')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    public static function updatedAtColumn(?string $name = 'updated_at'): TextColumn
    {
        return TextColumn::make($name)
            ->label(__('fields.updated_at'))
            ->dateTime('Y-m-d, H:i:s')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    public static function notesColumn(?string $name = 'notes'): TextColumn
    {
        return TextColumn::make($name)
            ->label(__('fields.notes'))
            ->wrap()
            ->searchable()
            ->toggleable();
    }

    public static function dateTimeColumn(?string $name = 'date_time'): TextColumn
    {
        return TextColumn::make($name)
            ->label(__('fields.date_time'))
            ->dateTime('Y-m-d, H:i')
            ->fontFamily('mono')
            ->sortable()
            ->toggleable();
    }
}
