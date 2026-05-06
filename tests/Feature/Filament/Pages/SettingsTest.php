<?php

declare(strict_types=1);

namespace Tests\Feature\Pages;

use App\Filament\Pages\Settings;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the settings page', function (): void {
    Http::fake([
        'https://api.github.com/repos/barthjs/fin-tracker/releases/latest' => Http::response([
            'tag_name' => 'v1.2.3',
        ], 200),
    ]);

    livewire(Settings::class)
        ->assertOk()
        ->assertSee('1.2.3')
        ->assertSet('latestVersion', '1.2.3');
});

it('exposes navigation metadata', function (): void {
    expect(Settings::getNavigationGroup())->toBeString()->not->toBeEmpty()
        ->and(Settings::getNavigationLabel())->toBeString()->not->toBeEmpty();

    Http::fake(['*' => Http::response(['tag_name' => 'v1.0.0'], 200)]);

    $page = livewire(Settings::class)->instance();

    expect($page->getTitle())->toBeString()->not->toBeEmpty()
        ->and($page->getHeading())->toBeString()->not->toBeEmpty();
});

it('handles a failed github response', function (): void {
    Http::fake(['*' => Http::response('error', 500)]);

    livewire(Settings::class)
        ->assertOk()
        ->assertSet('latestVersion', null);
});

it('handles a github response without a tag name', function (): void {
    Http::fake(['*' => Http::response(['foo' => 'bar'], 200)]);

    livewire(Settings::class)
        ->assertOk()
        ->assertSet('latestVersion', null);
});

it('handles a github connection exception', function (): void {
    Http::fake(fn () => throw new ConnectionException('connection refused'));

    livewire(Settings::class)
        ->assertOk()
        ->assertSet('latestVersion', null);
});
