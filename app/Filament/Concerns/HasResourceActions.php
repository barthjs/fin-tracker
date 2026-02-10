<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Carbon\Carbon;
use Filament\Actions\BulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
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

    public static function tableBulkEditAction(string $name): BulkAction
    {
        return BulkAction::make($name)
            ->icon('tabler-edit')
            ->after(fn (Component $livewire) => $livewire->dispatch('refreshInfolist'))
            ->deselectRecordsAfterCompletion();
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

    public static function typeFilter(): SelectFilter
    {
        return SelectFilter::make('type')
            ->label(__('fields.type'));
    }

    public static function accountFilter(): SelectFilter
    {
        return SelectFilter::make('account_id')
            ->label(Str::ucfirst(__('account.label')))
            ->relationship('account', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->multiple()
            ->preload()
            ->searchable();
    }

    public static function categoryFilter(): SelectFilter
    {
        return SelectFilter::make('category_id')
            ->label(Str::ucfirst(__('category.label')))
            ->relationship('category', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->multiple()
            ->preload()
            ->searchable();
    }

    public static function portfolioFilter(): SelectFilter
    {
        return SelectFilter::make('portfolio_id')
            ->label(Str::ucfirst(__('portfolio.label')))
            ->relationship('portfolio', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->multiple()
            ->preload()
            ->searchable();
    }

    public static function securityFilter(): SelectFilter
    {
        return SelectFilter::make('security_id')
            ->label(Str::ucfirst(__('security.label')))
            ->relationship('security', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
            ->multiple()
            ->preload()
            ->searchable();
    }

    public static function dateTimeRangeFilter(string $column = 'date_time'): Filter
    {
        return Filter::make('date_range')
            ->schema([
                DatePicker::make('from')
                    ->label(__('table.filter.from'))
                    ->default(Carbon::today()->startOfYear()),

                DatePicker::make('until')
                    ->label(__('table.filter.until')),
            ])
            ->columns(2)
            ->query(function (Builder $query, array $data) use ($column): Builder {
                /** @var array{from: string, until: string} $data */
                return $query
                    ->when(
                        $data['from'],
                        fn (Builder $query, string $date): Builder => $query->whereDate($column, '>=', $date)
                    )
                    ->when(
                        $data['until'],
                        fn (Builder $query, string $date): Builder => $query->whereDate($column, '<=', $date)
                    );
            });
    }

    #[On('refreshInfolist')]
    public function refresh(): void {}
}
