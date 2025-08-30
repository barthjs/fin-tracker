<?php

declare(strict_types=1);

return [

    'label' => 'Depot',
    'plural_label' => 'Depots',
    'slug' => 'depots',

    'buttons' => [
        'bulk_edit_portfolio' => 'Portfolio bearbeiten',
    ],

    'import' => [
        'modal_heading' => 'Depots importieren',
        'failure_heading' => 'Depotimport fehlgeschlagen',
        'success_heading' => 'Depotimport erfolgreich',
        'body_heading' => 'Der Depotimport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreich importierte Zeilen: ',
    ],

    'export' => [
        'modal_heading' => 'Depots exportieren',
        'failure_heading' => 'Depotexport fehlgeschlagen',
        'success_heading' => 'Depotexport erfolgreich',
        'body_heading' => 'Der Depotexport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreiche exportierte Zeilen: ',
        'file_name' => 'Depots_',
    ],

];
