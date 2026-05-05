<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TradeType;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Illuminate\Database\Eloquent\Collection;

/**
 * Recalculate and persist the market value for a portfolio.
 *
 * The market value is derived from all trades in the portfolio:
 * - BUY trades increase position (positive quantity)
 * - SELL trades decrease position (negative quantity)
 * - Market value is calculated as 'sum(quantity * current security price)'
 */
final class RecalculatePortfolioMarketValue
{
    public function __invoke(string $portfolioId): void
    {
        // Aggregate quantities per security
        $quantities = Trade::query()->where('portfolio_id', $portfolioId)
            ->selectRaw(
                'security_id, SUM(CASE WHEN type = ? THEN quantity ELSE -quantity END) as total_quantity',
                [TradeType::Buy->value]
            )
            ->groupBy(['security_id'])
            ->get();

        if ($quantities->isEmpty()) {
            Portfolio::query()->whereKey($portfolioId)->update(['market_value' => 0.0]);

            return;
        }

        $securities = Security::query()->whereIn('id', $quantities->pluck('security_id')->all())
            ->get()
            ->keyBy('id');

        $marketValue = 0.0;

        /** @var Collection<int, Trade> $quantities */
        foreach ($quantities as $item) {
            /** @var Security $security */
            $security = $securities->get($item->security_id);
            /** @var string $totalQuantity */
            $totalQuantity = $item['total_quantity'];
            $marketValue += (float) $totalQuantity * $security->price;
        }

        Portfolio::query()->whereKey($portfolioId)->update(['market_value' => $marketValue]);
    }
}
