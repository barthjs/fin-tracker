<?php

return [

    'name' => 'Fin-Tracker',
    'version' => env('APP_VERSION'),

    'maintenance' => [
        'driver' => 'cache',
        'store' => 'database',
    ],

    'currency' => env('APP_DEFAULT_CURRENCY', 'USD'),

];
