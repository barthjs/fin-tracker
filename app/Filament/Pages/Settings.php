<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        $this->latestVersion = self::getLatestVersion();
    }

    /**
     * Get the latest tagged version from the GitHub repository.
     *
     * @return string|null Returns the version tag (e.g., "v1.2.3") or null on failure
     */
    public static function getLatestVersion(): ?string
    {
        return Cache::remember('github.latest_version', now()->addHour(), function () {
            try {
                $response = Http::retry(3, 100)
                    ->timeout(10)
                    ->withHeaders([
                        'Accept' => 'application/vnd.github.v3+json',
                        'User-Agent' => 'fin-tracker',
                    ])
                    ->get('https://api.github.com/repos/barthjs/fin-tracker/releases/latest');

                if (! $response->successful()) {
                    Log::warning('Failed to fetch latest version from GitHub API', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);

                    return null;
                }

                $data = $response->json();

                if (! isset($data['tag_name'])) {
                    Log::warning('GitHub API response missing tag_name field', ['response' => $data]);

                    return null;
                }

                return mb_ltrim($data['tag_name'], 'v');
            } catch (Throwable $e) {
                Log::error('Exception occurred while fetching latest version from GitHub', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return null;
            }
        });
    }
}
