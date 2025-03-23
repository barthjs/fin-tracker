<?php

declare(strict_types=1);

return [

    'slug' => 'umsaetze',
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
        'account_validation_message' => 'Bitte ein Konto angeben',
        'category_placeholder' => 'Kategorie wählen',
        'category_validation_message' => 'Bitte eine Kategorie angeben',
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
            'file_name' => 'Umsätze_',
        ],
    ],

    'empty' => 'Keine Umsätze gefunden',

];
