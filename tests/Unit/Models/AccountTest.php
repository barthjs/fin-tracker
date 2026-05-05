<?php

declare(strict_types=1);

use App\Models\Account;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

it('returns the owner of the account', function (): void {
    $account = Account::factory()->create();

    expect($account->user->id)->toBe(auth()->user()->id);
});

it('trims the name when creating', function (): void {
    $account = Account::factory()->create(['name' => '  Savings  ']);

    expect($account->name)->toBe('Savings');
    assertDatabaseHas('accounts', ['id' => $account->id, 'name' => 'Savings']);
});

it('trims the name when updating', function (): void {
    $account = Account::factory()->create(['name' => 'Savings']);

    $account->update(['name' => '  Checking  ']);

    expect($account->fresh()?->name)->toBe('Checking');
});

it('assigns the authenticated user when no user is given', function (): void {
    $account = Account::factory()->create();

    expect($account->user_id)->toBe(auth()->id());
});

it('sums the balances of active accounts only', function (): void {
    Account::factory()->create(['balance' => 100.0, 'is_active' => true]);
    Account::factory()->create(['balance' => 50.5, 'is_active' => true]);
    Account::factory()->create(['balance' => 999.0, 'is_active' => false]);

    expect(Account::getActiveBalanceSum())->toBe(150.5);
});

it('exposes a balance color based on the balance', function (float $balance, string $color): void {
    $account = Account::factory()->create(['balance' => $balance]);

    expect($account->balanceColor)->toBe($color);
})->with([
    'zero' => [0.0, 'gray'],
    'negative' => [-10.0, 'danger'],
    'positive' => [10.0, 'success'],
]);
