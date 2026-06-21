<?php

declare(strict_types=1);

use App\Enums\Country;
use Illuminate\Support\Facades\App;

it('translates country labels by locale', function (): void {
    App::setLocale('de');
    expect(Country::DE->getLabel())->toBe('Deutschland');

    App::setLocale('en');
    expect(Country::DE->getLabel())->toBe('Germany');
});

it('validates iso 3166-1 alpha-2 codes', function (): void {
    expect(Country::tryFrom('DE'))->toBe(Country::DE)
        ->and(Country::tryFrom('ZZ'))->toBeNull();
});

it('sorts options by translated label rather than declaration order', function (): void {
    App::setLocale('en');
    $positions = array_flip(array_keys(Country::options()));

    // "Netherlands" sorts before "Switzerland", even though CH is declared before NL.
    expect($positions[Country::NL->value])->toBeLessThan($positions[Country::CH->value]);
});
