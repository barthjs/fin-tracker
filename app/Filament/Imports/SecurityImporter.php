<?php

namespace App\Filament\Imports;

use App\Models\Security;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SecurityImporter extends Importer
{
    protected static ?string $model = Security::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label(__('security.columns.name'))
                ->exampleHeader(__('security.columns.name'))
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('isin')
                ->label(__('security.columns.isin'))
                ->exampleHeader(__('security.columns.isin'))
                ->rules(['max:255']),
            ImportColumn::make('symbol')
                ->label(__('security.columns.symbol'))
                ->exampleHeader(__('security.columns.symbol'))
                ->rules(['max:255']),
            ImportColumn::make('price')
                ->label(__('security.columns.price'))
                ->exampleHeader(__('security.columns.price'))
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (Security $record, string $state): void {
                    // Sanitize the input by removing all characters except digits, commas, periods, and signs.
                    $sanitized = preg_replace('/[^0-9,.+-]/', '', $state);

                    if (empty($sanitized) || $sanitized === '-' || $sanitized === '+') {
                        // Handle cases where the input is empty or just a sign.
                        $floatValue = 0.0;
                    } else {
                        $sign = 1;
                        if (str_starts_with($sanitized, '-')) {
                            $sign = -1; // Set sign for negative numbers
                            $sanitized = substr($sanitized, 1);
                        } elseif (str_starts_with($sanitized, '+')) {
                            $sanitized = substr($sanitized, 1);
                        }

                        // Handle different formats with both period and comma present.
                        if (str_contains($sanitized, '.') && str_contains($sanitized, ',')) {
                            if (strrpos($sanitized, '.') < strrpos($sanitized, ',')) {
                                // Assume period as thousands separator, replace comma with period for decimal
                                $sanitized = str_replace(['.', ','], ['', '.'], $sanitized);
                            } else {
                                // Assume comma as thousands separator
                                $sanitized = str_replace(',', '', $sanitized);
                            }
                        } else {
                            // Treat comma as a decimal separator if present
                            $sanitized = str_replace(',', '.', $sanitized);
                        }

                        // Convert sanitized string to a float and apply the sign.
                        $floatValue = (float)$sanitized * $sign;
                    }

                    // Assign the parsed float value to the record's amount property.
                    $record->price = $floatValue;
                }),
            ImportColumn::make('type')
                ->label(__('security.columns.type'))
                ->exampleHeader(__('security.columns.type'))
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->fillRecordUsing(function (Security $record, string $state): void {
                    $record->type = match ($state) {
                        __('security.types.BOND') => 'BOND',
                        __('security.types.DERIVATIVE') => 'DERIVATIVE',
                        __('security.types.ETF') => 'ETF',
                        __('security.types.FUND') => 'FUND',
                        __('security.types.STOCK') => 'STOCK',
                        default => ''
                    };
                }),
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
                ->label(__('security.columns.description'))
                ->exampleHeader(__('security.columns.description'))
                ->rules(['max:1000']),
        ];
    }

    public function resolveRecord(): Security
    {
        return Security::firstOrNew([
            'name' => trim($this->data['name']),
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = __('security.notifications.import.body_heading') . "\n\r" .
            __('security.notifications.import.body_success') . number_format($import->successful_rows);

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= "\n\r" . __('security.notifications.import.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'security-import';
    }
}
