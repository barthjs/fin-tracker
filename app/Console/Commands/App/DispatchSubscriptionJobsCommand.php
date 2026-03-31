<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use App\Services\SubscriptionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:dispatch-subscription-jobs')]
#[Description('Dispatch subscription jobs')]
final class DispatchSubscriptionJobsCommand extends Command
{
    public function handle(SubscriptionService $service): int
    {
        $service->dispatchReminders();
        $service->dispatchDueSubscriptions();

        return self::SUCCESS;
    }
}
