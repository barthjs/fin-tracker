<?php

declare(strict_types=1);

namespace App\Filament\Resources\Portfolios\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Exports\PortfolioExporter;
use App\Filament\Imports\PortfolioImporter;
use App\Filament\Resources\Portfolios\PortfolioResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\Rules\File;

final class ListPortfolios extends ListRecords
{
    use HasResourceActions;

    protected static string $resource = PortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::createAction(),

            self::importAction()
                ->modalHeading(__('portfolio.import.modal_heading'))
                ->importer(PortfolioImporter::class)
                ->failureNotificationTitle(__('portfolio.import.failure_heading'))
                ->successNotificationTitle(__('portfolio.import.success_heading'))
                ->fileRules([File::types(['csv'])->max(1024)]),

            self::exportAction()
                ->modalHeading(__('portfolio.export.modal_heading'))
                ->exporter(PortfolioExporter::class)
                ->failureNotificationTitle(__('portfolio.export.failure_heading'))
                ->successNotificationTitle(__('portfolio.export.success_heading')),
        ];
    }
}
