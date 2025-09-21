<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Enums\CategoryGroup;
use App\Filament\Concerns\HasResourceImportColumns;
use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Database\Eloquent\Builder;

final class CategoryImporter extends Importer
{
    use HasResourceImportColumns;

    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn()
                ->examples(__('category.import.examples.name')),

            ImportColumn::make('group')
                ->label(__('category.fields.group'))
                ->exampleHeader(__('category.fields.group'))
                ->examples(__('category.import.examples.group'))
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(function (string $state): CategoryGroup {
                    return match ($state) {
                        CategoryGroup::FixExpenses->getLabel() => CategoryGroup::FixExpenses,
                        CategoryGroup::VarExpenses->getLabel() => CategoryGroup::VarExpenses,
                        CategoryGroup::FixRevenues->getLabel() => CategoryGroup::FixRevenues,
                        CategoryGroup::VarRevenues->getLabel() => CategoryGroup::VarRevenues,
                        CategoryGroup::Transfers->getLabel() => CategoryGroup::Transfers,
                        default => CategoryGroup::VarExpenses,
                    };
                }),

            self::colorColumn(),
            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('category.import.body_heading')."\n\r".
            __('category.import.body_success').number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r".__('category.import.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function resolveRecord(): Category
    {
        return Category::query()
            ->where('user_id', auth()->user()->id)
            ->where('name', $this->data['name'])
            ->where('group', $this->data['group'])
            ->when(! empty($this->data['color']), function (Builder $query): void {
                $query->where('color', $this->data['color']);
            })->first() ?? new Category([
                'name' => $this->data['name'],
                'group' => $this->data['group'],
                'color' => $this->data['color'] ?? mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => auth()->user()->id,
            ]);
    }

    public function getJobBatchName(): string
    {
        return 'category-import';
    }
}
