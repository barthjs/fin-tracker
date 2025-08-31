<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Filament\Concerns\HasResourceExportColumns;
use App\Models\Category;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

final class CategoryExporter extends Exporter
{
    use HasResourceExportColumns;

    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            self::nameColumn(),

            ExportColumn::make('group')
                ->label(__('category.fields.group'))
                ->formatStateUsing(fn (CategoryGroup $state): string => $state->getLabel()),

            ExportColumn::make('type')
                ->label(__('fields.type'))
                ->formatStateUsing(fn (TransactionType $state): string => $state->getLabel()),

            self::colorColumn(),
            self::statusColumn(),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('category.export.body_heading')."\n\r".
            __('category.export.body_success').number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r".__('category.export.body_failure').number_format($failedRowsCount);
        }

        return $body;
    }

    public function getFileName(Export $export): string
    {
        return __('category.export.file_name').Carbon::now()->format('Y-m-d-H-i');
    }

    public function getJobBatchName(): string
    {
        return 'category-export';
    }
}
