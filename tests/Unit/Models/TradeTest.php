<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

it('returns the owner of the trade', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create();

    $trade = Trade::factory()->create([
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);

    expect($trade->user->id)->toBe(auth()->user()->id);
});

it('falls back to a default account when none is given', function (): void {
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create();

    $trade = Trade::factory()->create([
        'account_id' => null,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);

    assertDatabaseHas('accounts', ['id' => $trade->account_id, 'name' => 'Demo']);
});

it('falls back to a default portfolio when none is given', function (): void {
    $account = Account::factory()->create();
    $security = Security::factory()->create();

    $trade = Trade::factory()->create([
        'account_id' => $account->id,
        'portfolio_id' => null,
        'security_id' => $security->id,
    ]);

    assertDatabaseHas('portfolios', ['id' => $trade->portfolio_id, 'name' => 'Demo']);
});

it('falls back to a default security when none is given', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();

    $trade = Trade::factory()->create([
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => null,
    ]);

    assertDatabaseHas('securities', ['id' => $trade->security_id, 'name' => 'Demo']);
});
