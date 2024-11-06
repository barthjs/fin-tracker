<?php

return [

    'slug' => 'wertpapiere',
    'navigation_label' => 'Wertpapiere',

    'buttons' => [
        'create_button_label' => 'Neues Wertpapier',
        'create_heading' => 'Neues Wertpapier hinzufügen',
        'edit_heading' => 'Wertpapier bearbeiten',
        'delete_heading' => 'Wertpapier löschen',
        'export_heading' => 'Wertpapier exportieren',
        'import_heading' => 'Wertpapier importieren',
    ],

    'columns' => [
        'logo' => 'Logo',
        'name' => 'Name',
        'isin' => 'ISIN',
        'symbol' => 'Symbol',
        'price' => 'Kurs',
        'total_quantity' => 'Anzahl',
        'description' => 'Beschreibung',
        'type' => 'Typ',
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Wertpapierimport fehlgeschlagen',
            'success_heading' => 'Wertpapierimport erfolgreich',
            'body_heading' => 'Der Wertpapierimport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreich importierte Zeilen: ',
        ],
        'export' => [
            'failure_heading' => 'Wertpapierexport fehlgeschlagen',
            'success_heading' => 'Wertpapierexport erfolgreich',
            'body_heading' => 'Der Wertpapierexport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreiche exportierte Zeilen: ',
            'file_name' => 'Wertpapiere_'
        ]
    ],

    'empty' => 'Keine Wertpapiere gefunden'

];
