<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities\Pages;

use App\Filament\Resources\Securities\SecurityResource;
use Filament\Actions\EditAction;
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
            EditAction::make()
                ->icon('tabler-edit')
                ->modalHeading(__('security.buttons.edit_heading')),
        ];
    }
}
