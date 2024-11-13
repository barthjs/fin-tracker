<?php

namespace App\Filament\Resources\TradeResource\Pages;

use App\Filament\Exports\TradeExporter;
use App\Filament\Resources\TradeResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTrades extends ListRecords
{
    protected static string $resource = TradeResource::class;

    public function getTitle(): string
    {
        return __('trade.navigation_label');
    }

    public function getHeading(): string
    {
        return __('trade.navigation_label');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('trade.buttons.create_button_label'))
                ->modalHeading(__('trade.buttons.create_heading')),
            ExportAction::make()
                ->icon('tabler-table-export')
                ->label(__('table.export'))
                ->color('warning')
                ->modalHeading(__('trade.buttons.export_heading'))
                ->exporter(TradeExporter::class)
                ->failureNotificationTitle(__('trade.notifications.export.failure_heading'))
                ->successNotificationTitle(__('trade.notifications.export.success_heading'))
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->withoutGlobalScopes()->where('user_id', auth()->id()))
        ];
    }
}
