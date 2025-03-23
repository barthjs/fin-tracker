<?php

declare(strict_types=1);

return [

    'default' => 'database',

    'stores' => [

        'database' => [
            'driver' => 'database',
            'table' => 'sys_cache',
            'lock_table' => 'sys_cache_locks',
        ],

    ],

    'prefix' => env('fin-tracker_cache_'),

];
