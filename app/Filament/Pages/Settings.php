<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class Settings extends Page
{
    protected string $view = 'filament.pages.settings';

    public ?string $latestVersion;

    public ?string $latestVersionUrl;

    public static function getSlug(?Panel $panel = null): string
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
        try {
            $response = Http::get('https://hub.docker.com/v2/repositories/barthjs/fin-tracker/tags');
        } catch (ConnectionException) {
            $this->latestVersion = null;

            return;
        }

        $this->latestVersion = $response->json()['results'][2]['name'] ?? '';
    }
}
