<?php

declare(strict_types=1);

Schedule::command('app:cleanup-filament')->everyThreeHours()->withoutOverlapping();

Schedule::command('queue:prune-batches')->everySixHours()->withoutOverlapping();
Schedule::command('queue:prune-failed')->everySixHours()->withoutOverlapping();
Schedule::command('queue:flush')->everySixHours()->withoutOverlapping();
