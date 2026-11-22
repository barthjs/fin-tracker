<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pwa;

use Filament\Facades\Filament;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\JsonResponse;

final readonly class ManifestController
{
    /**
     * Returns the web app manifest with the name of the installation and the colors of the panel.
     *
     * Browsers paint the bars of an installed app in the theme color, which stays dark, since the bar at the bottom
     * keeps the color of the installation and cannot follow the color mode of the page.
     */
    public function __invoke(): JsonResponse
    {
        // Filament registers the colors of the panel only while it serves a panel request
        FilamentColor::register(Filament::getDefaultPanel()->getColors());

        /** @var array<int, string> $gray */
        $gray = FilamentColor::getColor('gray');
        $darkBackground = Color::convertToHex($gray[950]);
        $name = config()->string('app.name');

        return response()->json([
            'id' => '/',
            'name' => $name,
            'short_name' => $name,
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => $darkBackground,
            'theme_color' => $darkBackground,
            'icons' => [
                [
                    'src' => '/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/icons/icon-maskable-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ], headers: [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
