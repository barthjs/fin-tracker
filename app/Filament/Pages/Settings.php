<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'tabler-settings';
    protected static string $view = 'filament.pages.settings';

    public static function getSlug(): string
    {
        return __('settings.slug');
    }

    public static function getNavigationLabel(): string
    {
        return __('settings.navigation_label');
    }

    public function getTitle(): string
    {
        return __('settings.navigation_label');
    }

    public function getHeading(): string
    {
        return __('settings.navigation_label');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->is_admin;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
