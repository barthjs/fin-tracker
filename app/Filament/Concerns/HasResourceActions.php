<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

trait HasResourceActions
{
    public static function importAction(): ImportAction
    {
        return ImportAction::make()
            ->icon('tabler-table-import')
            ->label(__('Import'))
            ->color('warning')
            ->fileRules([File::types(['csv'])->max(1024)]);
    }

    public static function exportAction(): ExportAction
    {
        return ExportAction::make()
            ->icon('tabler-table-export')
            ->label(__('Export'))
            ->color('warning');
    }

    public static function createAction(): CreateAction
    {
        return CreateAction::make()
            ->icon('tabler-plus');
    }

    public static function editAction(): CreateAction
    {
        return CreateAction::make()
            ->icon('tabler-edit');
    }

    public static function deleteAction(): CreateAction
    {
        return DeleteAction::make()
            ->icon('tabler-edit');
    }

    public static function tableEditAction(): EditAction
    {
        return EditAction::make()
            ->iconButton()
            ->icon('tabler-edit');
    }

    public static function tableDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->iconButton()
            ->icon('tabler-trash');
    }

    public static function inactiveFilter(): Filter
    {
        return Filter::make('inactive')
            ->label(__('fields.status_inactive'))
            ->toggle()
            ->query(fn (Builder $query): Builder => $query->where('is_active', false));
    }
}
