<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Support\Colors\Color;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('serves the manifest to every actor', function (bool $isAuthenticated): void {
    if ($isAuthenticated) {
        actingAs(User::factory()->create());
    }

    get(route('pwa.manifest'))
        ->assertOk();
})->with([
    'guest' => false,
    'user' => true,
]);

it('returns the name of the installation and the dark background of the panel', function (): void {
    config()->set('app.name', fake()->word());

    get(route('pwa.manifest'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/manifest+json')
        ->assertJson([
            'name' => config('app.name'),
            'short_name' => config('app.name'),
            'background_color' => Color::convertToHex(Color::Zinc[950]),
            'theme_color' => Color::convertToHex(Color::Zinc[950]),
        ]);
});

it('lists only icons that exist', function (): void {
    /** @var list<array{src: string}> $icons */
    $icons = get(route('pwa.manifest'))->json('icons');

    foreach ($icons as $icon) {
        expect(public_path($icon['src']))->toBeFile();
    }
});

it('starts no session', function (): void {
    get(route('pwa.manifest'))
        ->assertCookieMissing(config()->string('session.cookie'));
});
