<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\GetOrCreateDefaultAccount;
use App\Actions\GetOrCreateDefaultPortfolio;
use App\Actions\GetOrCreateDefaultSecurity;
use App\Models\Trade;

final readonly class TradeObserver
{
    public function __construct(
        private GetOrCreateDefaultAccount $getOrCreateDefaultAccount,
        private GetOrCreateDefaultPortfolio $getOrCreateDefaultPortfolio,
        private GetOrCreateDefaultSecurity $getOrCreateDefaultSecurity,
    ) {}

    public function creating(Trade $trade): void
    {
        // Only needed in importer
        /** @phpstan-ignore-next-line */
        if ($trade->account_id === null) {
            $trade->account_id = ($this->getOrCreateDefaultAccount)()->id;
        }

        // Only needed in importer
        /** @phpstan-ignore-next-line */
        if ($trade->portfolio_id === null) {
            $trade->portfolio_id = ($this->getOrCreateDefaultPortfolio)()->id;
        }

        // Only needed in importer
        /** @phpstan-ignore-next-line */
        if ($trade->security_id === null) {
            $trade->security_id = ($this->getOrCreateDefaultSecurity)()->id;
        }
    }
}
