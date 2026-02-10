<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

final class DispatchSubscriptionJobsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:dispatch-subscription-jobs';

    /**
     * @var string
     */
    protected $description = 'Dispatch subscription jobs';

    public function handle(SubscriptionService $service): int
    {
        $service->dispatchReminders();
        $service->dispatchDueSubscriptions();

        return self::SUCCESS;
    }
}
