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
                ->label(__('transaction_category.columns.name'))
                ->exampleHeader(__('transaction_category.columns.name'))
                ->examples(__('transaction_category.columns.name_examples'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('group')
                ->label(__('transaction_category.columns.group'))
                ->exampleHeader(__('transaction_category.columns.group'))
                ->examples(__('transaction_category.columns.group_examples'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (TransactionCategory $record, string $state): void {
                    $record->group = match ($state) {
                        __('transaction_category.groups.fix_expenses') => 'fix_expenses',
                        __('transaction_category.groups.var_expenses') => 'var_expenses',
                        __('transaction_category.groups.fix_revenues') => 'fix_revenues',
                        __('transaction_category.groups.var_revenues') => 'var_revenues',
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
        $body = __('transaction_category.notifications.import.body_heading') . "\n\r" .
            __('transaction_category.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('transaction_category.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'transaction-category-import';
    }
}
