<?php

return [

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'sys_password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

];
