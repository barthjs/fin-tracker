<?php

declare(strict_types=1);

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    // one month
    'lifetime' => 43800,

    'table' => env('SESSION_TABLE', 'sys_sessions'),

    'cookie' => 'fin-tracker_session',

];
