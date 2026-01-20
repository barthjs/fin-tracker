<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Illuminate\Support\Facades\DB;

final class SecurityService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Security $security, array $data): Security
    {
        return DB::transaction(function () use ($security, $data): Security {
            $oldPrice = $security->price;

            $security->update($data);

            if ($oldPrice !== $security->price) {
                /** @var array<string> $portfolios */
                $portfolios = Trade::where('security_id', $security->id)
                    ->distinct(['portfolio_id'])
                    ->pluck('portfolio_id')
                    ->toArray();

                if (! empty($portfolios)) {
                    foreach ($portfolios as $portfolio) {
                        Portfolio::updatePortfolioMarketValue($portfolio);
                    }
                }
            }

            return $security;
        });
    }
}
