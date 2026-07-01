<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\PeriodUnit;
use App\Enums\TransactionType;
use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Budget;
use App\Models\Category;
use App\Services\BudgetService;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\Rule;

final class BudgetImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Budget::class;

    public static function getColumns(): array
    {
        return [
            self::numericColumn('amount')
                ->label(__('budget.fields.amount'))
                ->rules(['required', 'numeric', 'gt:0', 'max:1e9']),

            ImportColumn::make('category')
                ->label(__('category.label'))
                ->requiredMapping()
                ->relationship(resolveUsing: 'name')
                ->rules(['required']),

            ImportColumn::make('period_unit')
                ->label(__('budget.fields.period_unit'))
                ->requiredMapping()
                ->rules(['required', Rule::enum(PeriodUnit::class)]),

            ImportColumn::make('period_frequency')
                ->label(__('budget.fields.period_frequency'))
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'min:1', 'max:365']),

            self::dateColumn('starts_at')
                ->label(__('budget.fields.starts_at')),

            self::dateColumn('ends_at')
                ->label(__('budget.fields.ends_at'))
                ->requiredMapping(false)
                ->rules(['nullable', 'after_or_equal:starts_at']),

            ImportColumn::make('warning_threshold')
                ->label(__('budget.fields.warning_threshold'))
                ->numeric()
                ->rules(['nullable', 'integer', 'min:1', 'max:99']),

            ImportColumn::make('notify_on_exceed')
                ->label(__('budget.fields.notify_on_exceed'))
                ->boolean()
                ->rules(['boolean']),

            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('budget.import.body_heading')."\n\r".
            __('budget.import.body_success').number_format($import->successful_rows);

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= "\n\r".__('budget.import.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function resolveRecord(): Budget
    {
        return new Budget;
    }

    /**
     * Saves the budget through the service after the checks that the panel and the API enforce on the category.
     */
    public function saveRecord(): void
    {
        /** @var Budget $budget */
        $budget = $this->record;

        $category = Category::query()->find($budget->category_id);

        if (! $category instanceof Category || $category->type !== TransactionType::Expense) {
            throw new RowImportFailedException(__('budget.validation.category_expense'));
        }

        if (Budget::query()->where('category_id', $category->id)->exists()) {
            throw new RowImportFailedException(__('budget.validation.category_unique'));
        }

        $service = resolve(BudgetService::class);

        $data = collect($this->record?->toArray() ?? [])
            ->except(['category'])
            ->toArray();

        /** @phpstan-ignore-next-line */
        $this->record = $service->create($data);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getJobBatchName(): string
    {
        return 'budget-import';
    }
}
