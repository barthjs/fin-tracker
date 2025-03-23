<?php

declare(strict_types=1);

return [

    'slug' => 'depot',
    'navigation_label' => 'Depots',

    'buttons' => [
        'create_button_label' => 'Neues Depot',
        'create_heading' => 'Neues Depot hinzufügen',
        'edit_heading' => 'Depot bearbeiten',
        'delete_heading' => 'Depot löschen',
        'export_heading' => 'Depots exportieren',
        'import_heading' => 'Depots importieren',
    ],

    'columns' => [
        'logo' => 'Logo',
        'name' => 'Name',
        'name_examples' => [
            'Deutsche Bank',
            'Commerzbank',
            'ING',
        ],
        'market_value' => 'Depotvolumen',
        'description' => 'Beschreibung',
        'description_examples' => [
            'Lorem ipsum dolor sit amet',
            'Lorem ipsum dolor sit amet',
            'Lorem ipsum dolor sit amet',
        ],
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Depotimport fehlgeschlagen',
            'success_heading' => 'Depotimport erfolgreich',
            'body_heading' => 'Der Depotimport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreich importierte Zeilen: ',
        ],
        'export' => [
            'failure_heading' => 'Depotexport fehlgeschlagen',
            'success_heading' => 'Depotexport erfolgreich',
            'body_heading' => 'Der Depotexport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreiche exportierte Zeilen: ',
            'file_name' => 'Depots_',
        ],
    ],

    'empty' => 'Keine Depots gefunden',

];
