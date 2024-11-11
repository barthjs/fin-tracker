<?php

return [

    'slug' => 'trades',
    'navigation_label' => 'Trades',

    'buttons' => [
        'create_button_label' => 'Neuer Umsatz',
        'create_heading' => 'Neuen Umsatz hinzufügen',
        'edit_heading' => 'Umsatz bearbeiten',
        'delete_heading' => 'Umsatz löschen',
        'bulk_delete_heading' => 'Ausgewählte Umsätze löschen',
        'bulk_account' => 'Konto bearbeiten',
        'bulk_portfolio' => 'Depot bearbeiten',
        'bulk_security' => 'Wertpapier bearbeiten',
        'export_heading' => 'Umsätze exportieren',
        'import_heading' => 'Umsätze importieren',
    ],

    'columns' => [
        'date' => 'Datum',
        'total_amount' => 'Gesamtbetrag',
        'quantity' => 'Anzahl',
        'price' => 'Kurs',
        'tax' => 'Steuern',
        'fee' => 'Gebühren',
        'type' => 'Typ',
        'notes' => 'Notizen',
        'account' => 'Konto',
        'portfolio' => 'Depot',
        'security' => 'Wertpapier',
    ],

    'form' => [
        'type_placeholder' => 'Typ wählen',
        'account_placeholder' => 'Konto wählen',
        'account_validation_message' => 'Bitte ein Konto angeben',
        'portfolio_placeholder' => 'Depot wählen',
        'portfolio_validation_message' => 'Bitte ein Depot angeben',
        'security_placeholder' => 'Wertpapier wählen',
        'security_validation_message' => 'Bitte ein Wertpapier angeben',
    ],

    'types' => [
        'BUY' => 'Kauf',
        'SELL' => 'Verkauf',
        'DIVIDEND' => 'Dividende'
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
