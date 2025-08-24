<?php

declare(strict_types=1);

return [

    'label' => 'Konto',
    'plural_label' => 'Konten',
    'slug' => 'konten',

    'fields' => [
        'balance' => 'Kontostand',
        'transfer_account_id' => 'Zielkonto',
    ],

    'buttons' => [
        'bulk_edit_account' => 'Konto bearbeiten',
    ],

    'import' => [
        'modal_heading' => 'Konten importieren',
        'failure_heading' => 'Kontoimport fehlgeschlagen',
        'success_heading' => 'Kontoimport erfolgreich',
        'body_heading' => 'Der Kontoimport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreich importierte Zeilen: ',

        'examples' => [
            'name' => [
                'Deutsche Bank',
                'Commerzbank',
                'ING',
            ],
            'currency' => [
                'EUR',
                'EUR',
                'EUR',
            ],
            'description' => [
                'Lorem ipsum dolor sit amet',
                'Lorem ipsum dolor sit amet',
                'Lorem ipsum dolor sit amet',
            ],
        ],
    ],

    'export' => [
        'modal_heading' => 'Konten exportieren',
        'failure_heading' => 'Kontoexport fehlgeschlagen',
        'success_heading' => 'Kontoexport erfolgreich',
        'body_heading' => 'Der Kontoexport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreiche exportierte Zeilen: ',
        'file_name' => 'Konten_',
    ],

];
