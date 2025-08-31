<?php

declare(strict_types=1);

return [

    'label' => 'Kategorie',
    'plural_label' => 'Kategorien',

    'fields' => [
        'group' => 'Gruppe',
    ],

    'group' => [
        'fix_expenses' => 'Fixe Ausgaben',
        'var_expenses' => 'Variable Ausgaben',
        'fix_revenues' => 'Fixe Einnahmen',
        'var_revenues' => 'Variable Einnahmen',
        'transfers' => 'Umbuchungen',
    ],

    'buttons' => [
        'bulk_edit_group' => 'Gruppe bearbeiten',
    ],

    'import' => [
        'modal_heading' => 'Kategorien importieren',
        'failure_heading' => 'Kategorien fehlgeschlagen',
        'success_heading' => 'Kategorienimport erfolgreich',
        'body_heading' => 'Der Kategorienimport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreich importierte Zeilen: ',

        'examples' => [
            'name' => [
                'Miete',
                'Freizeit',
                'Gehalt',
                'Zinsen',
                'Umbuchung',
            ],
            'group' => [
                'Fixe Ausgaben',
                'Variable Ausgaben',
                'Fixe Einnahmen',
                'Variable Einnahmen',
                'Umbuchungen',
            ],
        ],
    ],

    'export' => [
        'modal_heading' => 'Kategorien exportieren',
        'failure_heading' => 'Kategorienexport fehlgeschlagen',
        'success_heading' => 'Kategorienexport erfolgreich',
        'body_heading' => 'Der Kategorienexport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreiche exportierte Zeilen: ',
        'file_name' => 'Kategorien_',
    ],

];
