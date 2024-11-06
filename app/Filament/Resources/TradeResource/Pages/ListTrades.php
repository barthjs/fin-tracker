<?php

namespace App\Filament\Resources\TradeResource\Pages;

use App\Filament\Resources\TradeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrades extends ListRecords
{
    protected static string $resource = TradeResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
        ];
    }
}
