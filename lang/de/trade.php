<?php

declare(strict_types=1);

return [

    'label' => 'Trade',
    'plural_label' => 'Trades',
    'slug' => 'trades',

    'fields' => [
        'total_amount' => 'Gesamtbetrag',
        'quantity' => 'Anzahl',
        'tax' => 'Steuern',
        'fee' => 'Gebühren',
    ],

    'type' => [
        'buy' => 'Kauf',
        'sell' => 'Verkauf',
    ],

    'import' => [
        'modal_heading' => 'Trades importieren',
        'failure_heading' => 'Tradesimport fehlgeschlagen',
        'success_heading' => 'Tradesimport erfolgreich',
        'body_heading' => 'Der Tradesimport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreich importierte Zeilen: ',
    ],

    'export' => [
        'modal_heading' => 'Trades exportieren',
        'failure_heading' => 'Tradesexport fehlgeschlagen',
        'success_heading' => 'Tradesexport erfolgreich',
        'body_heading' => 'Der Tradesexport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreiche exportierte Zeilen: ',
        'file_name' => 'Trades_',
    ],

];
