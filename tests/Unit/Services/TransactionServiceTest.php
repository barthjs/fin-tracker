<?php

declare(strict_types=1);

use App\Services\TransactionService;

beforeEach(fn () => asUser());

it('no-ops the transaction service bulk methods on empty collections', function (): void {
    $service = resolve(TransactionService::class);

    $service->bulkEditAccount(collect(), ['account_id' => 'missing']);
    $service->bulkDelete(collect());

    expect(true)->toBeTrue();
});
