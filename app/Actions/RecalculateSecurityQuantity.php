<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TradeType;
use App\Models\Security;
use App\Models\Trade;

/**
 * Recalculate and persist the total quantity held for a security.
 *
 * The quantity is the difference between all bought and sold quantities.
 */
final class RecalculateSecurityQuantity
{
    public function __invoke(string $securityId): void
    {
        $security = Security::query()->find($securityId);
        if (! $security instanceof Security) {
            return;
        }

        $buys = (float) Trade::query()->where('security_id', $securityId)
            ->where('type', TradeType::Buy)
            ->sum('quantity');

        $sells = (float) Trade::query()->where('security_id', $securityId)
            ->where('type', TradeType::Sell)
            ->sum('quantity');

        $totalQuantity = $buys - $sells;

        $security->update(['total_quantity' => $totalQuantity]);
    }
}
