<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities\Pages;

use App\Filament\Resources\Securities\SecurityResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewSecurity extends ViewRecord
{
    protected static string $resource = SecurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
        ];
    }
}
