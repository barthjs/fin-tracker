<?php

declare(strict_types=1);

use App\Actions\GetOrCreateDefaultSecurity;
use App\Models\Security;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

it('creates a Demo security when none exists', function (): void {
    $security = resolve(GetOrCreateDefaultSecurity::class)();

    expect($security->name)->toBe('Demo')
        ->and($security->user_id)->toBe(auth()->id());
    assertDatabaseHas('securities', ['name' => 'Demo', 'user_id' => auth()->id()]);
});

it('returns the existing Demo security without creating a new one', function (): void {
    $existing = Security::factory()->create(['name' => 'Demo']);

    $security = resolve(GetOrCreateDefaultSecurity::class)();

    expect($security->id)->toBe($existing->id)
        ->and(Security::query()->where('name', 'Demo')->count())->toBe(1);
});

it('creates the default security for the given user', function (): void {
    $other = User::factory()->create();

    $security = resolve(GetOrCreateDefaultSecurity::class)($other);

    expect($security->user_id)->toBe($other->id)
        ->and($security->name)->toBe('Demo');
});
