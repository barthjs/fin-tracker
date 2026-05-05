<?php

declare(strict_types=1);

use App\Actions\RecalculatePortfolioMarketValue;
use App\Enums\TradeType;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;

beforeEach(fn () => asUser());

it('derives the market value from net holdings priced at the current security price', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $securityA = Security::factory()->create(['price' => 10.0]);
    $securityB = Security::factory()->create(['price' => 5.0]);

    // Net 3 of security A (4 bought, 1 sold)
    Trade::factory()->create([
        'type' => TradeType::Buy,
        'quantity' => 4.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $securityA->id,
    ]);
    Trade::factory()->create([
        'type' => TradeType::Sell,
        'quantity' => 1.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $securityA->id,
    ]);
    // Net 2 of security B
    Trade::factory()->create([
        'type' => TradeType::Buy,
        'quantity' => 2.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $securityB->id,
    ]);

    resolve(RecalculatePortfolioMarketValue::class)($portfolio->id);

    // 3 * 10 + 2 * 5 = 40
    expect($portfolio->fresh()?->market_value)->toBe(40.0);
});

it('resets the market value to zero for an empty portfolio', function (): void {
    $portfolio = Portfolio::factory()->create(['market_value' => 999.0]);

    resolve(RecalculatePortfolioMarketValue::class)($portfolio->id);

    expect($portfolio->fresh()?->market_value)->toBe(0.0);
});
