<?php

declare(strict_types=1);

namespace App\Filament\Resources\SecurityResource\Pages;

use App\Filament\Resources\SecurityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSecurity extends ViewRecord
{
    protected static string $resource = SecurityResource::class;

    public function getTitle(): string
    {
        return __('security.navigation_label');
    }

    public function getHeading(): string
    {
        return __('security.navigation_label');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit')
                ->modalHeading(__('security.buttons.edit_heading')),
        ];
    }
}
