<?php

declare(strict_types=1);

return [

    'label' => 'Abonnement',
    'plural_label' => 'Abonnements',

    'fields' => [
        'period_unit' => 'Einheit',
        'period_frequency' => 'Frequenz',
        'day_of_month' => 'Tag des Monats',
        'started_at' => 'Startdatum',
        'next_payment_date' => 'Nächste Zahlung',
        'due_until' => 'Fällig bis',
        'ended_at' => 'Enddatum',
        'auto_generate_transaction' => 'Automatisch Transaktion erstellen',
        'last_generated_at' => 'Zuletzt erstellt',
        'last_generated_at_placeholder' => 'Nie',
    ],

    'interval' => [
        'single' => [
            'day' => 'Täglich',
            'week' => 'Wöchentlich',
            'month' => 'Monatlich',
            'year' => 'Jährlich',
        ],
        'multiple' => 'Alle :count :unit',
    ],

    'units' => [
        'day' => 'Tag|Tage',
        'week' => 'Woche|Wochen',
        'month' => 'Monat|Monate',
        'year' => 'Jahr|Jahre',
    ],

    'hints' => [
        'next_payment' => 'Datum der nächsten fälligen Ausführung.',
        'auto_generate' => 'Wenn aktiviert, erstellt das System am Stichtag automatisch eine Transaktion.',
    ],

    'generated_note' => 'Automatisch erstellt für Dauerbuchung vom :date.',

    'notifications' => [
        'generated_title' => 'Dauerbuchung ausgeführt',
        'generated_body' => 'Die Buchung für ":name" über :amount wurde erfolgreich erstellt.',
        'catch_up_title' => ':count Zahlungen nachgeholt',
        'catch_up_body' => 'Es wurden :count ausstehende Zahlungen für :name (Gesamt: :amount) erfolgreich nachgeholt.',
        'failed_title' => 'Dauerbuchung fehlgeschlagen',
        'failed_body' => 'Die automatische Buchung für ":name" konnte nicht erstellt werden.',
    ],

];
