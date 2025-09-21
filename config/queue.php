<?php

declare(strict_types=1);

return [

    'default' => 'database',

    'connections' => [

        'database' => [
            'driver' => 'database',
            'table' => 'sys_jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],

    ],

    'batching' => [
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'sys_job_batches',
    ],

    'failed' => [
        'driver' => 'database-uuids',
        'database' => env('DB_CONNECTION', 'pgsql'),
        'table' => 'sys_failed_jobs',
    ],

];
