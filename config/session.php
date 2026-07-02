<?php

declare(strict_types=1);

return [

    // one month
    'lifetime' => (int) env('SESSION_LIFETIME', 43800),

    'table' => env('SESSION_TABLE', 'sys_sessions'),

    'cookie' => 'fin-tracker_session',

];
