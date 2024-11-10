<?php

namespace App\Filament\Resources\SecurityTypeResource\Pages;

use App\Filament\Resources\SecurityTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSecurityTypes extends ListRecords
{
    protected static string $resource = SecurityTypeResource::class;

    public function getTitle(): string
    {
        return __('security_type.navigation_label');
    }

    public function getHeading(): string
    {
        return __('security_type.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('tabler-plus')
                ->label(__('security_type.buttons.create_button_label'))
                ->modalHeading(__('security_type.buttons.create_heading')),
        ];
    }
}
