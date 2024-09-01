<?php

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'table' => env('SESSION_TABLE', 'sys_sessions'),

    'encrypt' => env('SESSION_ENCRYPT', true),
];
