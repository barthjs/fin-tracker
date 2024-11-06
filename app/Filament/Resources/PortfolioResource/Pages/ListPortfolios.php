<?php

namespace App\Filament\Resources\PortfolioResource\Pages;

use App\Filament\Resources\PortfolioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPortfolios extends ListRecords
{
    protected static string $resource = PortfolioResource::class;

    public function getTitle(): string
    {
        return __('portfolio.navigation_label');
    }

    public function getHeading(): string
    {
        return __('portfolio.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('portfolio.buttons.create_button_label'))
                ->modalHeading(__('portfolio.buttons.create_heading')),
        ];
    }
}
