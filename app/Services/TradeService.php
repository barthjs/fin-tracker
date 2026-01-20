<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TradeType;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TradeService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Trade
    {
        return DB::transaction(function () use ($data): Trade {
            /** @var Trade $trade */
            $trade = Trade::create($data);

            $this->refreshContext([$trade->account_id], [$trade->portfolio_id], [$trade->security_id]);

            return $trade;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Trade $trade, array $data): Trade
    {
        return DB::transaction(function () use ($trade, $data): Trade {
            /** @var TradeType $oldType */
            $oldType = $trade->getOriginal('type');
            /** @var float $oldAmount */
            $oldAmount = $trade->getOriginal('total_amount');
            /** @var float $oldQuantity */
            $oldQuantity = $trade->getOriginal('quantity');
            /** @var string $oldAccountId */
            $oldAccountId = $trade->getOriginal('account_id');
            /** @var string $oldPortfolioId */
            $oldPortfolioId = $trade->getOriginal('portfolio_id');
            /** @var string $oldSecurityId */
            $oldSecurityId = $trade->getOriginal('security_id');

            $trade->update($data);
            $trade->refresh();

            $accountIds = [];
            if ($oldAccountId !== $trade->account_id) {
                $accountIds = [$oldAccountId, $trade->account_id];
            } elseif ($oldType !== $trade->type || $oldAmount !== $trade->total_amount) {
                $accountIds = [$trade->account_id];
            }

            $portfolioIds = [];
            if ($oldPortfolioId !== $trade->portfolio_id) {
                $portfolioIds = [$oldPortfolioId, $trade->portfolio_id];
            } elseif ($oldType !== $trade->type || $oldQuantity !== $trade->quantity) {
                $portfolioIds = [$trade->portfolio_id];
            }

            $securityIds = [];
            if ($oldSecurityId !== $trade->security_id) {
                $securityIds = [$oldSecurityId, $trade->security_id];
            } elseif ($oldType !== $trade->type || $oldQuantity !== $trade->quantity) {
                $securityIds = [$trade->security_id];
            }

            $this->refreshContext($accountIds, $portfolioIds, $securityIds);

            return $trade;
        });
    }

    public function delete(Trade $trade): void
    {
        DB::transaction(function () use ($trade): void {
            $accountId = $trade->account_id;
            $portfolioId = $trade->portfolio_id;
            $securityId = $trade->security_id;

            $trade->delete();

            $this->refreshContext([$accountId], [$portfolioId], [$securityId]);
        });
    }

    /**
     * @param  Collection<int, Trade>  $trades
     * @param  array{
     *     account_id?: string,
     *     portfolio_id?: string,
     *     security_id?: string,
     * }  $data
     */
    public function bulkUpdate(Collection $trades, array $data): void
    {
        if ($trades->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($trades, $data): void {
            /** @var array<string> $accountIds */
            $accountIds = $trades->pluck('account_id')->toArray();
            /** @var array<string> $portfolioIds */
            $portfolioIds = $trades->pluck('portfolio_id')->toArray();
            /** @var array<string> $securityIds */
            $securityIds = $trades->pluck('security_id')->toArray();

            Trade::query()
                ->whereIn('id', $trades->pluck('id')->toArray())
                ->update($data);

            $accountIds[] = $data['account_id'] ?? null;
            $portfolioIds[] = $data['portfolio_id'] ?? null;
            $securityIds[] = $data['security_id'] ?? null;

            $this->refreshContext($accountIds, $portfolioIds, $securityIds);
        });
    }

    /**
     * @param  Collection<int, Trade>  $trades
     */
    public function bulkDelete(Collection $trades): void
    {
        if ($trades->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($trades): void {
            /** @var array<string> $accountIds */
            $accountIds = $trades->pluck('account_id')->toArray();
            /** @var array<string> $portfolioIds */
            $portfolioIds = $trades->pluck('portfolio_id')->toArray();
            /** @var array<string> $securityIds */
            $securityIds = $trades->pluck('security_id')->toArray();

            Trade::query()
                ->whereIn('id', $trades->pluck('id')->toArray())
                ->delete();

            $this->refreshContext($accountIds, $portfolioIds, $securityIds);
        });
    }

    /**
     * @param  array<string|null>  $accountIds
     * @param  array<string|null>  $portfolioIds
     * @param  array<string|null>  $securityIds
     */
    private function refreshContext(array $accountIds, array $portfolioIds, array $securityIds): void
    {
        foreach (array_unique(array_filter($accountIds)) as $id) {
            Account::updateAccountBalance($id);
        }

        foreach (array_unique(array_filter($portfolioIds)) as $id) {
            Portfolio::updatePortfolioMarketValue($id);
        }

        foreach (array_unique(array_filter($securityIds)) as $id) {
            Security::updateSecurityQuantity($id);
        }
    }
}
