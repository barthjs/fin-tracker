<?php

declare(strict_types=1);

use App\Actions\RecalculateSecurityQuantity;
use App\Enums\TradeType;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Illuminate\Support\Str;

beforeEach(fn () => asUser());

it('derives the total quantity from buys minus sells', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create();

    Trade::factory()->create([
        'type' => TradeType::Buy,
        'quantity' => 10.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);
    Trade::factory()->create([
        'type' => TradeType::Buy,
        'quantity' => 5.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);
    Trade::factory()->create([
        'type' => TradeType::Sell,
        'quantity' => 3.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);

    resolve(RecalculateSecurityQuantity::class)($security->id);

    expect($security->fresh()?->total_quantity)->toBe(12.0);
});

it('does nothing when the security does not exist', function (): void {
    resolve(RecalculateSecurityQuantity::class)(Str::ulid()->toString());

    expect(Security::query()->count())->toBe(0);
});
