<?php

declare(strict_types=1);

namespace App\Filament\Resources\SecurityResource\Pages;

use App\Filament\Exports\SecurityExporter;
use App\Filament\Imports\SecurityImporter;
use App\Filament\Resources\SecurityResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

class ListSecurities extends ListRecords
{
    protected static string $resource = SecurityResource::class;

    public function getTitle(): string
    {
        return __('security.navigation_label');
    }

    public function getHeading(): string
    {
        return __('security.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('security.buttons.create_button_label'))
                ->modalHeading(__('security.buttons.create_heading')),
            ImportAction::make()
                ->icon('tabler-table-import')
                ->label(__('table.import'))
                ->color('warning')
                ->modalHeading(__('security.buttons.import_heading'))
                ->importer(SecurityImporter::class)
                ->failureNotificationTitle(__('security.notifications.import.failure_heading'))
                ->successNotificationTitle(__('security.notifications.import.success_heading'))
                ->fileRules([File::types(['csv'])->max(1024)]),
            ExportAction::make()
                ->icon('tabler-table-export')
                ->label(__('table.export'))
                ->color('warning')
                ->modalHeading(__('security.buttons.export_heading'))
                ->exporter(SecurityExporter::class)
                ->failureNotificationTitle(__('security.notifications.export.failure_heading'))
                ->successNotificationTitle(__('security.notifications.export.success_heading'))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes()->where('user_id', auth()->id())),
        ];
    }
}
