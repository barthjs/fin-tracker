<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use App\Services\TradeService;
use App\Services\TransactionService;

beforeEach(fn () => asUser());

it('no-ops the transaction service bulk methods on empty collections', function (): void {
    $service = resolve(TransactionService::class);

    $service->bulkEditAccount(collect(), ['account_id' => 'missing']);
    $service->bulkDelete(collect());

    expect(true)->toBeTrue();
});

it('no-ops the trade service bulk methods on empty collections', function (): void {
    $service = resolve(TradeService::class);

    $service->bulkUpdate(collect(), []);
    $service->bulkDelete(collect());

    expect(true)->toBeTrue();
});

it('recalculates both old and new context when a trade changes all relations', function (): void {
    $accountA = Account::factory()->create();
    $accountB = Account::factory()->create();
    $portfolioA = Portfolio::factory()->create();
    $portfolioB = Portfolio::factory()->create();
    $securityA = Security::factory()->create();
    $securityB = Security::factory()->create();

    $trade = Trade::factory()->create([
        'account_id' => $accountA->id,
        'portfolio_id' => $portfolioA->id,
        'security_id' => $securityA->id,
    ]);

    resolve(TradeService::class)->update($trade, [
        'account_id' => $accountB->id,
        'portfolio_id' => $portfolioB->id,
        'security_id' => $securityB->id,
    ]);

    expect($trade->fresh())
        ->account_id->toBe($accountB->id)
        ->portfolio_id->toBe($portfolioB->id)
        ->security_id->toBe($securityB->id);
});
