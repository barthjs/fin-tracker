<?php

declare(strict_types=1);

namespace App\Filament\Resources\Budgets\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Exports\BudgetExporter;
use App\Filament\Imports\BudgetImporter;
use App\Filament\Resources\Budgets\BudgetResource;
use Filament\Resources\Pages\ListRecords;

final class ListBudgets extends ListRecords
{
    use HasResourceActions;

    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            BudgetResource::configureCreateAction(),

            self::importAction()
                ->modalHeading(__('budget.import.modal_heading'))
                ->importer(BudgetImporter::class)
                ->failureNotificationTitle(__('budget.import.failure_heading'))
                ->successNotificationTitle(__('budget.import.success_heading')),

            self::exportAction()
                ->modalHeading(__('budget.export.modal_heading'))
                ->exporter(BudgetExporter::class)
                ->failureNotificationTitle(__('budget.export.failure_heading'))
                ->successNotificationTitle(__('budget.export.success_heading')),
        ];
    }
}
