<?php

declare(strict_types=1);

use App\Enums\SecurityRegion;
use App\Models\Security;
use Illuminate\Support\Facades\App;

it('sorts options by translated label rather than declaration order', function (): void {
    App::setLocale('en');
    $positions = array_flip(array_keys(SecurityRegion::options()));

    // "Asia Pacific" sorts before "Europe" alphabetically, even though Europe is declared first.
    expect($positions[SecurityRegion::AsiaPacific->value])->toBeLessThan($positions[SecurityRegion::Europe->value]);
});

it('resolves a case from its value or label', function (): void {
    App::setLocale('en');

    expect(SecurityRegion::tryFromValueOrLabel('europe'))->toBe(SecurityRegion::Europe)
        ->and(SecurityRegion::tryFromValueOrLabel(' ASIA PACIFIC '))->toBe(SecurityRegion::AsiaPacific)
        ->and(SecurityRegion::tryFromValueOrLabel('unknown'))->toBeNull();
});

it('orders a query using the same order as options() with null values last', function (): void {
    $positions = array_flip(array_keys(SecurityRegion::options()));

    $sql = SecurityRegion::orderQueryUsing(Security::query(), 'region', 'desc')->toBase()->orders[0]['sql'];

    expect($sql)->toContain('CASE WHEN "region" IS NULL THEN '.count($positions))
        ->and($sql)->toContain("WHEN 'asia_pacific' THEN ".$positions[SecurityRegion::AsiaPacific->value])
        ->and($sql)->toEndWith('desc');
});
