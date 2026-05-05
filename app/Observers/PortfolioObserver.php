<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Portfolio;

final class PortfolioObserver
{
    public function creating(Portfolio $portfolio): void
    {
        $portfolio->name = mb_trim($portfolio->name);

        /** @phpstan-ignore-next-line */
        if ($portfolio->user_id === null) {
            $portfolio->user_id = auth()->user()->id;
        }
    }

    public function updating(Portfolio $portfolio): void
    {
        $portfolio->name = mb_trim($portfolio->name);
    }
}
