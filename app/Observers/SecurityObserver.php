<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Security;

final class SecurityObserver
{
    public function creating(Security $security): void
    {
        $this->trimFields($security);

        /** @phpstan-ignore-next-line */
        if ($security->user_id === null) {
            $security->user_id = auth()->user()->id;
        }
    }

    public function updating(Security $security): void
    {
        $this->trimFields($security);
    }

    private function trimFields(Security $security): void
    {
        $security->name = mb_trim($security->name);
        $security->isin = $security->isin === null ? null : mb_trim($security->isin);
        $security->symbol = $security->symbol === null ? null : mb_trim($security->symbol);
        $security->description = $security->description === null ? null : mb_trim($security->description);
    }
}
