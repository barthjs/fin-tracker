<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewCategory extends ViewRecord
{
    use HasResourceActions;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::editAction(),
            self::deleteAction(),
        ];
    }
}
