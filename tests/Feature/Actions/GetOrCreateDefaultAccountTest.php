<?php

declare(strict_types=1);

use App\Actions\GetOrCreateDefaultAccount;
use App\Models\Account;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

it('creates a Demo account when none exists', function (): void {
    $account = resolve(GetOrCreateDefaultAccount::class)();

    expect($account->name)->toBe('Demo')
        ->and($account->user_id)->toBe(auth()->id());
    assertDatabaseHas('accounts', ['name' => 'Demo', 'user_id' => auth()->id()]);
});

it('returns the existing Demo account without creating a new one', function (): void {
    $existing = Account::factory()->create(['name' => 'Demo']);

    $account = resolve(GetOrCreateDefaultAccount::class)();

    expect($account->id)->toBe($existing->id)
        ->and(Account::query()->where('name', 'Demo')->count())->toBe(1);
});

it('creates the default account for the given user', function (): void {
    $other = User::factory()->create();

    $account = resolve(GetOrCreateDefaultAccount::class)($other);

    expect($account->user_id)->toBe($other->id)
        ->and($account->name)->toBe('Demo');
});
