<?php

declare(strict_types=1);

namespace App\Filament\Resources\PortfolioResource\Pages;

use App\Filament\Resources\PortfolioResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPortfolio extends ViewRecord
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
            EditAction::make()
                ->icon('tabler-edit')
                ->modalHeading(__('portfolio.buttons.edit_heading')),
        ];
    }
}
