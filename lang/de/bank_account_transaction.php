<?php

return [

    'url' => 'umsaetze',
    'navigation_label' => 'Umsätze',

    'buttons' => [
        'create_button_label' => 'Neuer Umsatz',
        'create_heading' => 'Neuen Umsatz hinzufügen',
        'edit_heading' => 'Umsatz bearbeiten',
        'delete_heading' => 'Umsatz löschen',
        'bulk_delete_heading' => 'Ausgewählte Umsätze löschen',
        'bulk_account' => 'Konto bearbeiten',
        'bulk_category' => 'Kategorie bearbeiten',
        'export_heading' => 'Umsätze exportieren',
        'import_heading' => 'Umsätze importieren',
    ],

    'columns' => [
        'date' => 'Datum',
        'amount' => 'Betrag',
        'destination' => 'Ziel',
        'notes' => 'Notizen',
        'account' => 'Konto',
        'category' => 'Kategorie',
        'group' => 'Gruppe',
        'type' => 'Typ',
    ],

    'form' => [
        'account_placeholder' => 'Konto wählen',
        'category_placeholder' => 'Kategorie wählen',
    ],

    'filter' => [
        'all' => 'Alle',
        'expenses' => 'Ausgaben',
        'fix_expenses' => 'Fixe Ausgaben',
        'var_expenses' => 'Variable Ausgaben',
        'revenues' => 'Einnahmen',
        'fix_revenues' => 'Fixe Einnahmen',
        'var_revenues' => 'Variable Einnahmen',
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Umsatzimport fehlgeschlagen',
            'success_heading' => 'Umsatzimport erfolgreich',
            'body_heading' => 'Der Umsatzimport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreich importierte Zeilen: ',
        ],
        'export' => [
            'failure_heading' => 'Umsatzexport fehlgeschlagen',
            'success_heading' => 'Umsatzexport erfolgreich',
            'body_heading' => 'Der Umsatzexport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreiche exportierte Zeilen: ',
            'file_name' => 'Umsätze_'
        ]
    ],

    'empty' => 'Keine Umsätze gefunden',

];
