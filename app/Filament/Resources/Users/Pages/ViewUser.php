<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewUser extends ViewRecord
{
    use HasResourceActions;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::editAction(),
            self::deleteAction(),
        ];
    }
}
