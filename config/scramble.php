<?php

declare(strict_types=1);

return [

    'export_path' => 'docs/api.json',

    'info' => [
        'version' => env('APP_VERSION', 'dev'),
    ],

    'ui' => [
        'theme' => 'system',
    ],

    'middleware' => ['web'],

];
