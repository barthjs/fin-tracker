<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Subscription;

final class SubscriptionObserver
{
    public function saving(Subscription $subscription): void
    {
        $subscription->name = mb_trim($subscription->name);
    }
}
