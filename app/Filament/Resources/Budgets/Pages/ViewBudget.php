<?php

declare(strict_types=1);

namespace App\Filament\Resources\Budgets\Pages;

use App\Actions\CalculateBudgetUsage;
use App\Data\BudgetUsage;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Budgets\BudgetResource;
use App\Models\Budget;
use App\Services\BudgetService;
use Filament\Resources\Pages\ViewRecord;

final class ViewBudget extends ViewRecord
{
    use HasResourceActions;

    protected static string $resource = BudgetResource::class;

    private ?BudgetUsage $usage = null;

    /**
     * Returns the usage of the viewed budget, calculated once per request for all infolist entries.
     */
    public function usage(): BudgetUsage
    {
        if (! $this->usage instanceof BudgetUsage) {
            /** @var Budget $budget */
            $budget = $this->getRecord();

            $this->usage = resolve(CalculateBudgetUsage::class)($budget);
        }

        return $this->usage;
    }

    protected function getHeaderActions(): array
    {
        return [
            self::editAction()
                /** @phpstan-ignore-next-line */
                ->action(fn (BudgetService $service, Budget $record, array $data): Budget => $service->update($record, $data)),

            self::deleteAction(),
        ];
    }
}
