<?php

declare(strict_types=1);

return [

    'label' => 'Benachrichtigungsziel',
    'plural_label' => 'Benachrichtigungsziele',

    'fields' => [
        'configuration' => 'Konfiguration',
        'is_default' => 'Standard',
    ],

    'configuration' => [
        'database' => [
            'label' => 'Intern',
        ],

        'generic_webhook' => [
            'label' => 'Webhook',
            'hint' => 'Ein POST Request wird an diese URL gesendet.',
            'url' => 'Webhook URL',
            'secret' => 'Signatur Secret (Optional)',
            'secret_hint' => 'Falls leer, wird der Standard-Systemschlüssel zur Signierung verwendet.',
            'verify_ssl' => 'SSL-Zertifikat überprüfen',
            'content_type' => 'Content Type',
            'content_type_json' => 'JSON (application/json)',
            'content_type_form' => 'Form URL Encoded (application/x-www-form-urlencoded)',
        ],
    ],

    'actions' => [
        'test_notification' => 'Verbindung testen',
        'ping_success' => 'Test-Benachrichtigung wurde erfolgreich versendet.',
        'ping_failed' => 'Versand der Test-Benachrichtigung fehlgeschlagen: :error',
    ],

    'test_payload' => [
        'title' => 'Test-Benachrichtigung',
        'body' => 'Dies ist eine Test-Benachrichtigung zur Überprüfung der Konfiguration für diesen Kanal.',
    ],

];
