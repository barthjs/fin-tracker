<?php

declare(strict_types=1);

use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;

beforeEach(fn () => asUser());

it('returns the owner of the security', function (): void {
    $security = Security::factory()->create();
    expect($security->user->id)->toBe(auth()->user()->id);
});

it('returns related portfolios via trades relation', function (): void {
    $security = Security::factory()->create();

    $portfolioA = Portfolio::factory()->create();
    $portfolioB = Portfolio::factory()->create();

    Trade::factory()->for($security)->for($portfolioA)->create();
    Trade::factory()->for($security)->for($portfolioB)->create();

    $portfolios = $security->portfolios;

    expect($portfolios)
        ->toHaveCount(2)
        ->pluck('id')
        ->toContain($portfolioA->id, $portfolioB->id);
});

it('trims the text fields when creating', function (): void {
    $security = Security::factory()->create([
        'name' => '  Apple  ',
        'isin' => '  US0378331005  ',
        'symbol' => '  AAPL  ',
        'description' => '  A tech company  ',
    ]);

    expect($security->name)->toBe('Apple')
        ->and($security->isin)->toBe('US0378331005')
        ->and($security->symbol)->toBe('AAPL')
        ->and($security->description)->toBe('A tech company');
});

it('trims the text fields when updating', function (): void {
    $security = Security::factory()->create(['name' => 'Apple']);

    $security->update(['name' => '  Microsoft  ']);

    expect($security->fresh()?->name)->toBe('Microsoft');
});

it('keeps nullable text fields null', function (): void {
    $security = Security::factory()->create([
        'isin' => null,
        'symbol' => null,
        'description' => null,
    ]);

    expect($security->isin)->toBeNull()
        ->and($security->symbol)->toBeNull()
        ->and($security->description)->toBeNull();
});

it('assigns the authenticated user when no user is given', function (): void {
    $security = Security::factory()->create();

    expect($security->user_id)->toBe(auth()->id());
});
