<?php

declare(strict_types=1);

return [

    'label' => 'Umsatz',
    'plural_label' => 'Umsätze',

    'fields' => [
        'amount' => 'Betrag',
        'payee' => 'Ziel',
    ],

    'type' => [
        'expense' => 'Ausgabe',
        'revenue' => 'Einnahme',
        'transfer' => 'Umbuchung',
    ],

    'import' => [
        'modal_heading' => 'Umsätze importieren',
        'failure_heading' => 'Umsatzimport fehlgeschlagen',
        'success_heading' => 'Umsatzimport erfolgreich',
        'body_heading' => 'Der Umsatzimport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreich importierte Zeilen: ',
    ],

    'export' => [
        'modal_heading' => 'Umsätze exportieren',
        'failure_heading' => 'Umsatzexport fehlgeschlagen',
        'success_heading' => 'Umsatzexport erfolgreich',
        'body_heading' => 'Der Umsatzexport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreiche exportierte Zeilen: ',
        'file_name' => 'Umsätze_',
    ],

];
