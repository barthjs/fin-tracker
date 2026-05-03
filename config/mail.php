<?php

declare(strict_types=1);

return [

    'default' => env('MAIL_MAILER', 'smtp'),

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS'),
        'name' => env('MAIL_FROM_NAME', 'Fin-Tracker'),
    ],

];
