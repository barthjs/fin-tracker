<?php

declare(strict_types=1);

return [

    'slug' => 'kategorien',
    'navigation_label' => 'Kategorien',

    'buttons' => [
        'create_button_label' => 'Neue Kategorie',
        'create_heading' => 'Neue Kategorie hinzufügen',
        'edit_heading' => 'Kategorie bearbeiten',
        'delete_heading' => 'Kategorie löschen',
        'bulk_group' => 'Gruppe bearbeiten',
        'export_heading' => 'Kategorien exportieren',
        'import_heading' => 'Kategorien importieren',
    ],

    'columns' => [
        'name' => 'Name',
        'name_examples' => [
            'Miete',
            'Freizeit',
            'Lebensmittel',
            'Mobilität',
            'Gehalt',
            'Zinsen',
            'Umbuchung',
        ],
        'group' => 'Gruppe',
        'group_examples' => [
            'Fixe Ausgaben',
            'Variable Ausgaben',
            'Variable Ausgaben',
            'Variable Ausgaben',
            'Fixe Einnahmen',
            'Variable Einnahmen',
            'Umbuchungen',
        ],
        'type' => 'Typ',
    ],

    'form' => [
        'group_placeholder' => 'Gruppe wählen',
    ],

    'types' => [
        'expense' => 'Ausgabe',
        'revenue' => 'Einnahme',
        'transfer' => 'Umbuchung',
    ],

    'groups' => [
        'fix_expenses' => 'Fixe Ausgaben',
        'var_expenses' => 'Variable Ausgaben',
        'fix_revenues' => 'Fixe Einnahmen',
        'var_revenues' => 'Variable Einnahmen',
        'transfers' => 'Umbuchungen',
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Kategorien fehlgeschlagen',
            'success_heading' => 'Kategorienimport erfolgreich',
            'body_heading' => 'Der Kategorienimport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreich importierte Zeilen: ',
        ],
        'export' => [
            'failure_heading' => 'Kategorienexport fehlgeschlagen',
            'success_heading' => 'Kategorienexport erfolgreich',
            'body_heading' => 'Der Kategorienexport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreiche exportierte Zeilen: ',
            'file_name' => 'Kategorien_',
        ],
    ],

    'empty' => 'Keine Kategorien gefunden',

];
