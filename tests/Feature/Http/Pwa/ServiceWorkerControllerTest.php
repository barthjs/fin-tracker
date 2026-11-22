<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->publicPath = sys_get_temp_dir().'/'.fake()->uuid();
    $this->script = fake()->sentence();

    File::ensureDirectoryExists($this->publicPath.'/build');
    File::put($this->publicPath.'/build/sw.js', $this->script);
    app()->usePublicPath($this->publicPath);
});

afterEach(function (): void {
    File::deleteDirectory($this->publicPath);
});

it('serves the service worker to every actor', function (bool $isAuthenticated): void {
    if ($isAuthenticated) {
        actingAs(User::factory()->create());
    }

    get(route('pwa.service-worker'))
        ->assertOk();
})->with([
    'guest' => false,
    'user' => true,
]);

it('returns the service worker of the build without caching it', function (): void {
    $response = get(route('pwa.service-worker'))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/javascript; charset=utf-8');

    expect($response->streamedContent())->toBe($this->script)
        ->and($response->headers->getCacheControlDirective('no-cache'))->toBeTrue();
});

it('returns 404 without a build', function (): void {
    File::delete($this->publicPath.'/build/sw.js');

    get(route('pwa.service-worker'))
        ->assertNotFound();
});

it('starts no session', function (): void {
    get(route('pwa.service-worker'))
        ->assertCookieMissing(config()->string('session.cookie'));
});
