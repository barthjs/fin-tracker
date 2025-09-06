<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

final class EditUser extends EditRecord
{
    use HasResourceActions;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::deleteAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? UserResource::getUrl('index');
    }
}
