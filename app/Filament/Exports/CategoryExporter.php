<?php declare(strict_types=1);

namespace App\Filament\Exports;

use App\Enums\TransactionGroup;
use App\Enums\TransactionType;
use App\Models\Category;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CategoryExporter extends Exporter
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label(__('category.columns.name')),
            ExportColumn::make('group')
                ->label(__('category.columns.group'))
                ->formatStateUsing(fn(TransactionGroup $state): string => __('category.groups')[$state->name]),
            ExportColumn::make('type')
                ->label(__('category.columns.type'))
                ->formatStateUsing(fn(TransactionType $state): string => __('category.types')[$state->name]),
            ExportColumn::make('color')
                ->label(__('widget.color')),
            ExportColumn::make('active')
                ->label(__('table.active'))
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('category.notifications.export.body_heading') . "\n\r" .
            __('category.notifications.export.body_success') . number_format($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n\r" . __('category.notifications.export.body_failure') . number_format($failedRowsCount);
        }

        return $body;
    }

    public function getJobBatchName(): ?string
    {
        return 'category-export';
    }

    public function getFileName(Export $export): string
    {
        return __('category.notifications.export.file_name') . Carbon::now()->format('Y-m-d-h-i');
    }
}
