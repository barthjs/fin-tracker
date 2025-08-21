<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('user.buttons.edit_heading');
    }

    public function getHeading(): string
    {
        return __('user.navigation_label');
    }

    public function getSubheading(): string
    {
        return __('user.buttons.edit_heading');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon('tabler-trash')
                ->modalHeading(__('user.buttons.delete_heading')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? UserResource::getUrl('index');
    }
}
