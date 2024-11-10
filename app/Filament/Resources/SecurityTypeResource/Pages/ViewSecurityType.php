<?php

namespace App\Filament\Resources\SecurityTypeResource\Pages;

use App\Filament\Resources\SecurityTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSecurityType extends ViewRecord
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
            EditAction::make()
                ->icon('tabler-edit')
                ->modalHeading(__('security_type.buttons.edit_heading')),
        ];
    }
}
