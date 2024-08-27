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
                ->rules(['max:255']),
            ImportColumn::make('type')
                ->rules(['max:255'])
                ->fillRecordUsing(function (TransactionCategory $record, string $state): void {
                    $type = match ($state) {
                        __('resources.transaction_categories.types.income') => 'income',
                        __('resources.transaction_categories.types.expense') => 'expense',
                        __('resources.transaction_categories.types.transfer') => 'transfer',
                        default => "transfer"
                    };
                    $record->type = $type;
                }),
            ImportColumn::make('group')
                ->rules(['max:255'])
                ->fillRecordUsing(function (TransactionCategory $record, string $state): void {
                    $group = match ($state) {
                        __('resources.transaction_categories.groups.var_expense') => 'var_expense',
                        __('resources.transaction_categories.groups.fix_expense') => 'fix_expense',
                        __('resources.transaction_categories.groups.income') => 'income',
                        __('resources.transaction_categories.groups.transfer') => 'transfer',
                        default => "transfer"
                    };
                    $record->group = $group;
                }),
        ];
    }

    public function resolveRecord(): ?TransactionCategory
    {
        return new TransactionCategory();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your transaction category import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
