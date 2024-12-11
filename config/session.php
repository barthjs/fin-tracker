<?php

return [

    'driver' => 'database',

    'table' => 'sys_sessions',

    'encrypt' => env('SESSION_ENCRYPT', true),

    'cookie' => 'fin-tracker_session',

    // one month
    'lifetime' => 43800,

];
