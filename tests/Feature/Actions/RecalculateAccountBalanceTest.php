<?php

declare(strict_types=1);

use App\Actions\RecalculateAccountBalance;
use App\Enums\TradeType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use App\Models\Transaction;

beforeEach(fn () => asUser());

it('derives the balance from transactions and trades', function (): void {
    $account = Account::factory()->create();
    $source = Account::factory()->create();
    $target = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create();

    Transaction::factory()->create([
        'type' => TransactionType::Revenue,
        'amount' => 100.0,
        'account_id' => $account->id,
    ]);
    Transaction::factory()->create([
        'type' => TransactionType::Expense,
        'amount' => 30.0,
        'account_id' => $account->id,
    ]);
    // Outgoing transfer (account is the source)
    Transaction::factory()->create([
        'type' => TransactionType::Transfer,
        'amount' => 20.0,
        'account_id' => $account->id,
        'transfer_account_id' => $target->id,
    ]);
    // Incoming transfer (account is the target)
    Transaction::factory()->create([
        'type' => TransactionType::Transfer,
        'amount' => 10.0,
        'account_id' => $source->id,
        'transfer_account_id' => $account->id,
    ]);
    // Buy trade: 10 * 5 = 50
    Trade::factory()->create([
        'type' => TradeType::Buy,
        'price' => 10.0,
        'quantity' => 5.0,
        'tax' => 0.0,
        'fee' => 0.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);
    // Sell trade: 15 * 1 = 15
    Trade::factory()->create([
        'type' => TradeType::Sell,
        'price' => 15.0,
        'quantity' => 1.0,
        'tax' => 0.0,
        'fee' => 0.0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);

    resolve(RecalculateAccountBalance::class)($account->id);

    // 100 - 30 - 20 + 10 - 50 + 15 = 25
    expect($account->fresh()?->balance)->toBe(25.0);
});

it('resets the balance to zero when there is no activity', function (): void {
    $account = Account::factory()->create(['balance' => 999.0]);

    resolve(RecalculateAccountBalance::class)($account->id);

    expect($account->fresh()?->balance)->toBe(0.0);
});

it('ignores activity of other accounts', function (): void {
    $account = Account::factory()->create();
    $other = Account::factory()->create();

    Transaction::factory()->create([
        'type' => TransactionType::Revenue,
        'amount' => 500.0,
        'account_id' => $other->id,
    ]);

    resolve(RecalculateAccountBalance::class)($account->id);

    expect($account->fresh()?->balance)->toBe(0.0);
});
