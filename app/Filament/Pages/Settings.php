<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'tabler-settings';
    protected static string $view = 'filament.pages.settings';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 1;
    public static function canAccess(): bool
    {
        return auth()->user()->is_admin;
    }
}
