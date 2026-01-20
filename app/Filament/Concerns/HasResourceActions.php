<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;
use Livewire\Attributes\On;
use Livewire\Component;

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

    public static function editAction(): EditAction
    {
        return EditAction::make()
            ->icon('tabler-edit');
    }

    public static function deleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->icon('tabler-trash');
    }

    public static function tableCreateAction(): CreateAction
    {
        return CreateAction::make()
            ->icon('tabler-plus')
            ->after(fn (Component $livewire) => $livewire->dispatch('refreshInfolist'));
    }

    public static function tableEditAction(): EditAction
    {
        return EditAction::make()
            ->iconButton()
            ->icon('tabler-edit')
            ->after(fn (Component $livewire) => $livewire->dispatch('refreshInfolist'));
    }

    public static function tableDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->iconButton()
            ->icon('tabler-trash')
            ->after(fn (Component $livewire) => $livewire->dispatch('refreshInfolist'));
    }

    public static function tableBulkEditAction(?string $name = null): BulkAction
    {
        return BulkAction::make($name)
            ->icon('tabler-edit')
            ->after(fn (Component $livewire) => $livewire->dispatch('refreshInfolist'));
    }

    public static function tableBulkDeleteAction(): DeleteBulkAction
    {
        return DeleteBulkAction::make()
            ->icon('tabler-trash')
            ->after(fn (Component $livewire) => $livewire->dispatch('refreshInfolist'));
    }

    public static function inactiveFilter(): Filter
    {
        return Filter::make('inactive')
            ->label(__('fields.status_inactive'))
            ->toggle()
            ->query(fn (Builder $query): Builder => $query->where('is_active', false));
    }

    #[On('refreshInfolist')]
    public function refresh(): void {}
}
