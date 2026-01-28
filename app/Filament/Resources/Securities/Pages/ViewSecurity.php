<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Securities\SecurityResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewSecurity extends ViewRecord
{
    use HasResourceActions;

    protected static string $resource = SecurityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::editAction(),
            self::deleteAction(),
        ];
    }
}
