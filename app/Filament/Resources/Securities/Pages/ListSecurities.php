<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Exports\SecurityExporter;
use App\Filament\Imports\SecurityImporter;
use App\Filament\Resources\Securities\SecurityResource;
use App\Filament\Resources\Securities\Widgets\SecurityChart;
use App\Filament\Resources\Securities\Widgets\SecurityRegionChart;
use App\Filament\Resources\Securities\Widgets\SecuritySectorChart;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

final class ListSecurities extends ListRecords
{
    use ExposesTableToWidgets;
    use HasResourceActions;

    protected static string $resource = SecurityResource::class;

    protected function getFooterWidgets(): array
    {
        return [
            SecurityChart::class,
            SecurityRegionChart::class,
            SecuritySectorChart::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            self::createAction(),

            self::importAction()
                ->modalHeading(__('security.import.modal_heading'))
                ->importer(SecurityImporter::class)
                ->failureNotificationTitle(__('security.import.failure_heading'))
                ->successNotificationTitle(__('security.import.success_heading')),

            self::exportAction()
                ->modalHeading(__('security.export.modal_heading'))
                ->exporter(SecurityExporter::class)
                ->failureNotificationTitle(__('security.export.failure_heading'))
                ->successNotificationTitle(__('security.export.success_heading')),
        ];
    }
}
