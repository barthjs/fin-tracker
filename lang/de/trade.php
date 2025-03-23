<?php

declare(strict_types=1);

return [

    'slug' => 'trades',
    'navigation_label' => 'Trades',

    'buttons' => [
        'create_button_label' => 'Neuer Trade',
        'create_heading' => 'Neuen Trade hinzufügen',
        'edit_heading' => 'Trade bearbeiten',
        'delete_heading' => 'Trade löschen',
        'bulk_delete_heading' => 'Ausgewählte Trades löschen',
        'bulk_account' => 'Konto bearbeiten',
        'bulk_portfolio' => 'Depot bearbeiten',
        'bulk_security' => 'Wertpapier bearbeiten',
        'export_heading' => 'Trades exportieren',
        'import_heading' => 'Trades importieren',
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
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Tradesimport fehlgeschlagen',
            'success_heading' => 'Tradesimport erfolgreich',
            'body_heading' => 'Der Tradesimport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreich importierte Zeilen: ',
        ],
        'export' => [
            'failure_heading' => 'Tradesexport fehlgeschlagen',
            'success_heading' => 'Tradesexport erfolgreich',
            'body_heading' => 'Der Tradesexport wurde abgeschlossen.',
            'body_failure' => 'Fehlgeschlagene Zeilen: ',
            'body_success' => 'Erfolgreiche exportierte Zeilen: ',
            'file_name' => 'Trades_',
        ],
    ],

    'empty' => 'Keine Trades gefunden',

];
