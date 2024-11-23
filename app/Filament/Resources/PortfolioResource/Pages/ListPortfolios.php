<?php declare(strict_types=1);

namespace App\Filament\Resources\PortfolioResource\Pages;

use App\Filament\Exports\PortfolioExporter;
use App\Filament\Imports\PortfolioImporter;
use App\Filament\Resources\PortfolioResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

class ListPortfolios extends ListRecords
{
    protected static string $resource = PortfolioResource::class;

    public function getTitle(): string
    {
        return __('portfolio.navigation_label');
    }

    public function getHeading(): string
    {
        return __('portfolio.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('portfolio.buttons.create_button_label'))
                ->modalHeading(__('portfolio.buttons.create_heading')),
            ImportAction::make()
                ->icon('tabler-table-import')
                ->label(__('table.import'))
                ->color('warning')
                ->modalHeading(__('portfolio.buttons.import_heading'))
                ->importer(PortfolioImporter::class)
                ->failureNotificationTitle(__('portfolio.notifications.import.failure_heading'))
                ->successNotificationTitle(__('portfolio.notifications.import.success_heading'))
                ->fileRules([File::types(['csv'])->max(1024)]),
            ExportAction::make()
                ->icon('tabler-table-export')
                ->label(__('table.export'))
                ->color('warning')
                ->modalHeading(__('portfolio.buttons.export_heading'))
                ->exporter(PortfolioExporter::class)
                ->failureNotificationTitle(__('portfolio.notifications.export.failure_heading'))
                ->successNotificationTitle(__('portfolio.notifications.export.success_heading'))
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->withoutGlobalScopes()->where('user_id', auth()->id()))
        ];
    }
}
