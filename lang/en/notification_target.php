<?php

declare(strict_types=1);

return [

    'label' => 'notification target',
    'plural_label' => 'notification targets',

    'fields' => [
        'configuration' => 'Configuration',
        'is_default' => 'Standard',
    ],

    'configuration' => [
        'database' => [
            'label' => 'Internal',
        ],

        'generic_webhook' => [
            'label' => 'Webhook',
            'hint' => 'A POST request will be sent to this URL.',
            'url' => 'Webhook URL',
            'secret' => 'Signature Secret (Optional)',
            'secret_hint' => 'If left empty, the default system secret will be used.',
            'verify_ssl' => 'Verify SSL Certificate',
            'content_type' => 'Content Type',
            'content_type_json' => 'JSON (application/json)',
            'content_type_form' => 'Form URL Encoded (application/x-www-form-urlencoded)',
        ],
    ],

    'actions' => [
        'test_notification' => 'Test connection',
        'ping_success' => 'Test notification sent successfully.',
        'ping_failed' => 'Test notification failed: :error',
    ],

    'test_payload' => [
        'title' => 'Test Notification',
        'body' => 'This is a test notification to verify the configuration for this channel.',
    ],

];
