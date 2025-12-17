<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class Settings extends Page
{
    public ?string $latestVersion;

    public ?string $latestVersionUrl;

    protected string $view = 'filament.pages.settings';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getTitle(): string
    {
        return __('settings.navigation_label');
    }

    public function getHeading(): string
    {
        return __('settings.navigation_label');
    }

    public function mount(): void
    {
        $this->latestVersion = $this->getLatestVersion();
    }

    /**
     * Get the latest tagged version from the GitHub repository.
     *
     * @return string|null Returns the version tag (e.g., "v1.2.3") or null on failure
     */
    private function getLatestVersion(): ?string
    {
        /** @var string|null */
        return Cache::remember('github.latest_version', now()->addHour(), function () {
            try {
                /** @var Response $response */
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
                if (is_array($data) && array_key_exists('tag_name', $data) && is_string($data['tag_name'])) {
                    return mb_ltrim($data['tag_name'], 'v');
                }

                Log::warning('GitHub API response missing or invalid tag_name field', ['response' => $data]);

                return null;

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
