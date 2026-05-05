<?php

declare(strict_types=1);

use App\Models\Portfolio;

beforeEach(fn () => asUser());

it('returns the owner of the portfolio', function (): void {
    $portfolio = Portfolio::factory()->create();

    expect($portfolio->user->id)->toBe(auth()->user()->id);
});

it('trims the name when creating', function (): void {
    $portfolio = Portfolio::factory()->create(['name' => '  Growth  ']);

    expect($portfolio->name)->toBe('Growth');
    $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id, 'name' => 'Growth']);
});

it('trims the name when updating', function (): void {
    $portfolio = Portfolio::factory()->create(['name' => 'Growth']);

    $portfolio->update(['name' => '  Value  ']);

    expect($portfolio->fresh()?->name)->toBe('Value');
});

it('assigns the authenticated user when no user is given', function (): void {
    $portfolio = Portfolio::factory()->create();

    expect($portfolio->user_id)->toBe(auth()->id());
});

it('sums the market value of active portfolios only', function (): void {
    Portfolio::factory()->create(['market_value' => 100.0, 'is_active' => true]);
    Portfolio::factory()->create(['market_value' => 25.25, 'is_active' => true]);
    Portfolio::factory()->create(['market_value' => 999.0, 'is_active' => false]);

    expect(Portfolio::getActiveMarketValueSum())->toBe(125.25);
});

it('exposes a market value color based on the market value', function (float $marketValue, string $color): void {
    $portfolio = Portfolio::factory()->create(['market_value' => $marketValue]);

    expect($portfolio->marketValueColor)->toBe($color);
})->with([
    'zero' => [0.0, 'gray'],
    'negative' => [-10.0, 'danger'],
    'positive' => [10.0, 'success'],
]);
