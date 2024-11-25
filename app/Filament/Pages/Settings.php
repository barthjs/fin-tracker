<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class Settings extends Page
{
    protected static string $view = 'filament.pages.settings';
    public ?string $latestVersion;
    public ?string $latestVersionUrl;

    public static function getSlug(): string
    {
        return __('settings.slug');
    }

    public function getTitle(): string
    {
        return __('settings.navigation_label');
    }

    public function getHeading(): string
    {
        return __('settings.navigation_label');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $response = Http::get('https://hub.docker.com/v2/repositories/barthjs/fin-tracker/tags?page_size=2');
        $data = json_decode($response->getBody(), true);
        $this->latestVersion = $data['results'][1]['name'] ?? "";
    }
}
