<?php

declare(strict_types=1);

return [

    'name' => 'Fin-Tracker',

    'version' => env('APP_VERSION', 'dev'),

    'webhook_secret' => env('APP_WEBHOOK_SECRET', ''),

    'url' => mb_rtrim(env('APP_URL', 'http://localhost'), '/'),

    'allow_registration' => (bool) env('APP_ALLOW_REGISTRATION', false),

    'currency' => env('APP_DEFAULT_CURRENCY', 'EUR'),

];
