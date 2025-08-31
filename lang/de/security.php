<?php

declare(strict_types=1);

return [

    'label' => 'Wertpapier',
    'plural_label' => 'Wertpapiere',

    'fields' => [
        'isin' => 'ISIN',
        'symbol' => 'Symbol',
        'total_quantity' => 'Anzahl',
    ],

    'type' => [
        'bond' => 'Anleihe',
        'derivative' => 'Derivat',
        'etf' => 'ETF',
        'fund' => 'Fond',
        'stock' => 'Aktie',
    ],

    'import' => [
        'modal_heading' => 'Wertpapiere importieren',
        'failure_heading' => 'Wertpapierimport fehlgeschlagen',
        'success_heading' => 'Wertpapierimport erfolgreich',
        'body_heading' => 'Der Wertpapierimport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreich importierte Zeilen: ',
    ],

    'export' => [
        'modal_heading' => 'Wertpapier exportieren',
        'failure_heading' => 'Wertpapierexport fehlgeschlagen',
        'success_heading' => 'Wertpapierexport erfolgreich',
        'body_heading' => 'Der Wertpapierexport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreiche exportierte Zeilen: ',
        'file_name' => 'Wertpapiere_',
    ],

];
