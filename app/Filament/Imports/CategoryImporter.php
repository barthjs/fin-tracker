<?php

namespace App\Filament\Imports;

use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CategoryImporter extends Importer
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label(__('category.columns.name'))
                ->exampleHeader(__('category.columns.name'))
                ->examples(__('category.columns.name_examples'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('group')
                ->label(__('category.columns.group'))
                ->exampleHeader(__('category.columns.group'))
                ->examples(__('category.columns.group_examples'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (Category $record, string $state): void {
                    $record->group = match ($state) {
                        __('category.groups.fix_expenses') => 'fix_expenses',
                        __('category.groups.var_expenses') => 'var_expenses',
                        __('category.groups.fix_revenues') => 'fix_revenues',
                        __('category.groups.var_revenues') => 'var_revenues',
                        default => 'transfers'
                    };
                }),
        ];
    }

    public function resolveRecord(): ?Category
    {
        return Category::firstOrNew([
            'name' => trim($this->data['name']),
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('category.notifications.import.body_heading') . "\n\r" .
            __('category.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('category.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'category-import';
    }
}