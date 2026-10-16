<?php

declare(strict_types=1);

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    'default' => env('LOG_CHANNEL', 'stdout'),

    'channels' => [

        'file' => [
            'driver' => 'daily',
            'path' => storage_path('app/logs/fin-tracker.log'),
            'days' => 14,
            'level' => env('LOG_LEVEL', 'warning'),
            'replace_placeholders' => true,
        ],

        'stdout' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'warning'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stdout',
            ],
            'formatter' => JsonFormatter::class,
            'processors' => [PsrLogMessageProcessor::class],
        ],

    ],

];
