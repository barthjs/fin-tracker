<?php

declare(strict_types=1);

return [

    'oidc' => [
        'label' => 'OIDC',
        'oidc_enabled' => (bool) env('OIDC_ENABLED', false),
        'base_url' => env('OIDC_BASE_URL'),
        'client_id' => env('OIDC_CLIENT_ID'),
        'client_secret' => env('OIDC_CLIENT_SECRET'),
        'redirect' => null,
    ],

];
