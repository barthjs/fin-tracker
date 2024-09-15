<?php

return [

    'name' => 'Fin-Tracker',

    'maintenance' => [
        'driver' => 'cache',
        'store' => 'database',
    ],

    'currency' => env('APP_DEFAULT_CURRENCY', 'USD'),

];
