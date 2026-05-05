<?php

declare(strict_types=1);

use App\Casts\TimezoneCast;
use App\Models\User;

it('returns the stored timezone string when getting', function (): void {
    $cast = new TimezoneCast;

    expect($cast->get(new User, 'timezone', 'Europe/Berlin', []))->toBe('Europe/Berlin');
});

it('falls back to UTC when the stored value is not a string', function (): void {
    $cast = new TimezoneCast;

    expect($cast->get(new User, 'timezone', null, []))->toBe('UTC');
});

it('accepts a valid timezone identifier when setting', function (): void {
    $cast = new TimezoneCast;

    expect($cast->set(new User, 'timezone', 'Europe/Berlin', []))->toBe('Europe/Berlin');
});

it('rejects an invalid timezone identifier', function (): void {
    $cast = new TimezoneCast;

    expect(fn (): string => $cast->set(new User, 'timezone', 'Mars/Phobos', []))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects a non-string timezone value', function (): void {
    $cast = new TimezoneCast;

    expect(fn (): string => $cast->set(new User, 'timezone', 123, []))
        ->toThrow(InvalidArgumentException::class);
});
