<?php

namespace App\Filament\Clusters\Settings;

use App\Filament\Clusters;
use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'tabler-settings';
    protected static string $view = 'filament.pages.settings';
    protected static ?string $cluster = Clusters\Settings::class;

    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }
}
