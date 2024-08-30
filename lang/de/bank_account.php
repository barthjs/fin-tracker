<?php

return [

    'url' => 'konten',
    'navigation_label' => 'Konten',

    'buttons' => [
        'create_button_label' => 'Neues Konto',
        'create_heading' => 'Neues Konto hinzufügen',
        'edit_heading' => 'Konto bearbeiten',
        'delete_heading' => 'Konto löschen',
        'bulk_currency' => 'Währung bearbeiten',
        'export_heading' => 'Konten exportieren',
        'import_heading' => 'Konten importieren',
    ],

    'columns' => [
        'name' => 'Name',
        'name_examples' => [
            'Deutsche Bank',
            'Commerzbank',
            'ING'
        ],
        'balance' => 'Kontostand',
        'currency' => 'Währung',
        'currency_examples' => [
            'EUR',
            'EUR',
            'EUR'
        ],
        'description' => 'Beschreibung',
        'description_examples' => [
            'Lorem ipsum dolor sit amet',
            'Lorem ipsum dolor sit amet',
            'Lorem ipsum dolor sit amet'
        ]
    ],

    'form' => [
        'currency_placeholder' => 'Währung wählen',
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Kontoimport fehlgeschlagen',
            'success_heading' => 'Kontoimport erfolgreich',
            'body_heading' => 'Der Kontoimport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreich importierte Zeilen: ',
        ],
        'export' => [
            'failure_heading' => 'Kontoexport fehlgeschlagen',
            'success_heading' => 'Kontoexport erfolgreich',
            'body_heading' => 'Der Kontoexport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreiche exportierte Zeilen: ',
            'file_name' => 'Konten_'
        ]
    ],

    'empty' => 'Keine Konten gefunden'

];
