<?php

declare(strict_types=1);

use App\Actions\GetOrCreateDefaultPortfolio;
use App\Models\Portfolio;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

it('creates a Demo portfolio when none exists', function (): void {
    $portfolio = resolve(GetOrCreateDefaultPortfolio::class)();

    expect($portfolio->name)->toBe('Demo')
        ->and($portfolio->user_id)->toBe(auth()->id());
    assertDatabaseHas('portfolios', ['name' => 'Demo', 'user_id' => auth()->id()]);
});

it('returns the existing Demo portfolio without creating a new one', function (): void {
    $existing = Portfolio::factory()->create(['name' => 'Demo']);

    $portfolio = resolve(GetOrCreateDefaultPortfolio::class)();

    expect($portfolio->id)->toBe($existing->id)
        ->and(Portfolio::query()->where('name', 'Demo')->count())->toBe(1);
});

it('creates the default portfolio for the given user', function (): void {
    $other = User::factory()->create();

    $portfolio = resolve(GetOrCreateDefaultPortfolio::class)($other);

    expect($portfolio->user_id)->toBe($other->id)
        ->and($portfolio->name)->toBe('Demo');
});
