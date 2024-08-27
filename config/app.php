<?php

return [

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'cache'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    'currency' => env('APP_DEFAULT_CURRENCY', 'USD'),

];
