<?php

return [

    'default' => 'database',

    'stores' => [
        'database' => [
            'driver' => 'database',
            'table' => 'sys_cache',
            'lock_table' => 'sys_cache_locks',
        ],
    ],

];
