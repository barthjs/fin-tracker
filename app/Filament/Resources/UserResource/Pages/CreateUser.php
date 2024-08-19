<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('resources.users.navigation_label');
    }

    public function getHeading(): string
    {
        return __('resources.users.navigation_label');
    }

    public function getSubheading(): string
    {
        return __('resources.users.create_heading');
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }
}
