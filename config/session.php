<?php

use Illuminate\Support\Str;

return [

    'driver' => 'database',

    'table' => 'sys_sessions',

    'encrypt' => env('SESSION_ENCRYPT', true),

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'fin-tracker'), '_') . '_session'
    ),

];
