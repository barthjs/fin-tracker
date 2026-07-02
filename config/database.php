<?php

declare(strict_types=1);

return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'database'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'fin-tracker'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

    ],

    'migrations' => [
        'table' => 'sys_migrations',
        'update_date_on_publish' => true,
    ],

];
