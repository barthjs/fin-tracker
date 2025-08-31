<?php

declare(strict_types=1);

namespace App\Filament\Resources\Trades\Pages;

use App\Enums\TradeType;
use App\Filament\Concerns\HasResourceActions;
use App\Filament\Exports\TradeExporter;
use App\Filament\Imports\TradeImporter;
use App\Filament\Resources\Trades\TradeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\File;

final class ListTrades extends ListRecords
{
    use HasResourceActions;

    protected static string $resource = TradeResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('table.filter.all')),

            TradeType::Buy->value => Tab::make()
                ->label(TradeType::Buy->getLabel())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', TradeType::Buy)),

            TradeType::Sell->value => Tab::make()
                ->label(TradeType::Sell->getLabel())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('type', TradeType::Sell)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            self::createAction(),

            self::importAction()
                ->modalHeading(__('trade.import.modal_heading'))
                ->importer(TradeImporter::class)
                ->failureNotificationTitle(__('trade.import.failure_heading'))
                ->successNotificationTitle(__('trade.import.success_heading'))
                ->fileRules([File::types(['csv'])->max(1024)]),

            self::exportAction()
                ->modalHeading(__('trade.export.modal_heading'))
                ->exporter(TradeExporter::class)
                ->failureNotificationTitle(__('trade.export.failure_heading'))
                ->successNotificationTitle(__('trade.export.success_heading')),
        ];
    }
}
