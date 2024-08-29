<?php

namespace App\Filament\Imports;

use App\Models\TransactionCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class TransactionCategoryImporter extends Importer
{
    protected static ?string $model = TransactionCategory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('group')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (TransactionCategory $record, string $state): void {
                    $record->group = match ($state) {
                        __('resources.transaction_categories.groups.fix_expenses') => 'fix_expenses',
                        __('resources.transaction_categories.groups.var_expenses') => 'var_expenses',
                        __('resources.transaction_categories.groups.fix_revenues') => 'fix_revenues',
                        __('resources.transaction_categories.groups.var_revenues') => 'var_revenues',
                        default => 'transfers'
                    };
                }),
        ];
    }

    public function resolveRecord(): ?TransactionCategory
    {
        return TransactionCategory::firstOrNew([
            'name' => $this->data['name'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your transaction category import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'transaction-category-import';
    }
}
