<?php

namespace App\Filament\Resources\SecurityTypeResource\Pages;

use App\Filament\Resources\SecurityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSecurityType extends ViewRecord
{
    protected static string $resource = SecurityTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
