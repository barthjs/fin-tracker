<?php

declare(strict_types=1);

return [
    'authelia' => [
        'label' => 'Authelia',
        'oidc_enabled' => (bool) env('AUTHELIA_OIDC_ENABLED', false),
        'base_url' => env('AUTHELIA_OIDC_URL'),
        'client_id' => env('AUTHELIA_OIDC_CLIENT_ID'),
        'client_secret' => env('AUTHELIA_OIDC_CLIENT_SECRET'),
        'redirect' => null,
    ],

    'authentik' => [
        'label' => 'Authentik',
        'oidc_enabled' => (bool) env('AUTHENTIK_OIDC_ENABLED', false),
        'base_url' => env('AUTHENTIK_OIDC_URL'),
        'client_id' => env('AUTHENTIK_OIDC_CLIENT_ID'),
        'client_secret' => env('AUTHENTIK_OIDC_CLIENT_SECRET'),
        'redirect' => null,
    ],

    'gitea' => [
        'label' => 'Gitea',
        'oidc_enabled' => (bool) env('GITEA_OIDC_ENABLED', false),
        'instance_uri' => mb_rtrim(env('GITEA_OIDC_URL').'/'),
        'client_id' => env('GITEA_OIDC_CLIENT_ID'),
        'client_secret' => env('GITEA_OIDC_CLIENT_SECRET'),
        'redirect' => null,
    ],

    'keycloak' => [
        'label' => 'Keycloak',
        'oidc_enabled' => (bool) env('KEYCLOAK_OIDC_ENABLED', false),
        'base_url' => env('KEYCLOAK_OIDC_BASE_URL'),
        'realms' => env('KEYCLOAK_OIDC_REALM', 'master'),
        'client_id' => env('KEYCLOAK_OIDC_CLIENT_ID'),
        'client_secret' => env('KEYCLOAK_OIDC_CLIENT_SECRET'),
        'redirect' => null,
    ],

    'oidc' => [
        'label' => 'OIDC',
        'oidc_enabled' => (bool) env('OIDC_ENABLED', false),
        'base_url' => env('OIDC_BASE_URL'),
        'client_id' => env('OIDC_CLIENT_ID'),
        'client_secret' => env('OIDC_CLIENT_SECRET'),
        'redirect' => null,
    ],

];
