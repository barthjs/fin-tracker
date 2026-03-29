<?php

declare(strict_types=1);

return [

    'default' => env('CACHE_STORE', 'database'),

    'stores' => [

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'sys_cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE', 'sys_cache_locks'),
        ],

    ],

    'prefix' => 'fin-tracker-cache-',

];
