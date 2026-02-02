<?php

declare(strict_types=1);

use App\Console\Commands\CleanupDataCommand;
use App\Console\Commands\DispatchDueSubscriptionJobsCommand;
use Illuminate\Queue\Console\FlushFailedCommand;
use Illuminate\Queue\Console\PruneBatchesCommand;
use Illuminate\Queue\Console\PruneFailedJobsCommand;
use Laravel\Sanctum\Console\Commands\PruneExpired;

Schedule::command(DispatchDueSubscriptionJobsCommand::class)->everyThirtyMinutes()->withoutOverlapping();

Schedule::command(CleanupDataCommand::class)->everyThreeHours()->withoutOverlapping();

Schedule::command(PruneBatchesCommand::class)->everySixHours()->withoutOverlapping();
Schedule::command(PruneFailedJobsCommand::class)->everySixHours()->withoutOverlapping();
Schedule::command(FlushFailedCommand::class)->everySixHours()->withoutOverlapping();

Schedule::command(PruneExpired::class, ['--hours' => 24])->everySixHours()->withoutOverlapping();
