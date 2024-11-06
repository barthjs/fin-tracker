<?php

namespace App\Filament\Resources\SecurityTypeResource\Pages;

use App\Filament\Resources\SecurityTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSecurityTypes extends ListRecords
{
    protected static string $resource = SecurityTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
