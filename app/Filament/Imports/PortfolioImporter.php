<?php

namespace App\Filament\Imports;

use App\Models\Portfolio;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PortfolioImporter extends Importer
{
    protected static ?string $model = Portfolio::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label(__('portfolio.columns.name'))
                ->exampleHeader(__('portfolio.columns.name'))
                ->examples(__('portfolio.columns.name_examples'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('logo')
                ->rules(['max:255']),
            ImportColumn::make('color')
                ->label(__('widget.color'))
                ->exampleHeader(__('widget.color'))
                ->examples(function (): array {
                    $colors = [];
                    for ($i = 1; $i <= 3; $i++) {
                        $colors[] = strtolower(sprintf("#%06X", mt_rand(0, 0xFFFFFF)));
                    }
                    return $colors;
                })
                ->rules(['regex:/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/']),
            ImportColumn::make('description')
                ->label(__('portfolio.columns.description'))
                ->exampleHeader(__('portfolio.columns.description'))
                ->examples(__('portfolio.columns.description_examples'))
                ->rules(['max:1000']),
        ];
    }

    public function resolveRecord(): ?Portfolio
    {
        return Portfolio::firstOrNew([
            'name' => trim($this->data['name']),
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('portfolio.notifications.import.body_heading') . "\n\r" .
            __('portfolio.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('portfolio.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'portfolio-import';
    }
}
