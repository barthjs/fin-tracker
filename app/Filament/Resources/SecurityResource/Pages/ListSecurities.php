<?php

namespace App\Filament\Resources\SecurityResource\Pages;

use App\Filament\Resources\SecurityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSecurities extends ListRecords
{
    protected static string $resource = SecurityResource::class;

    public function getTitle(): string
    {
        return __('security.navigation_label');
    }

    public function getHeading(): string
    {
        return __('security.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('security.buttons.create_button_label'))
                ->modalHeading(__('security.buttons.create_heading'))
        ];
    }
}
