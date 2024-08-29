<?php

return [

    "transaction_categories" => [
        "navigation_label" => "Transaktionskategorien",
        "create_label" => "Neue Transaktionskategorie",
        "create_heading" => "Neue Transaktionskategorie erstellen",
        "edit_heading" => "Transaktionskategorie bearbeiten",
        "delete_heading" => "Transaktionskategorie löschen",

        "table" => [
            'name' => 'Name',
            'type' => 'Typ',
            'group' => 'Gruppe',
            'empty' => 'Keine Kategorien gefunden'
        ],

        "form" => [
            'type_placeholder' => 'Typ wählen',
            'group_placeholder' => 'Gruppe wählen',
        ],

        "types" => [
            'expense' => 'Ausgabe',
            'revenue' => 'Einnahme',
            'transfer' => 'Umbuchung'
        ],

        "groups" => [
            'fix_expenses' => 'Fixe Ausgaben',
            'var_expenses' => 'Variable Ausgaben',
            'fix_revenues' => 'Fixe Einnahmen',
            'var_revenues' => 'Variable Einnahmen',
            'transfers' => 'Umbuchungen',
        ]
    ],

    "bank_account_transactions" => [
        "navigation_label" => "Kontoumsätze",
        "create_label" => "Neuer Umsatz",
        "create_heading" => "Neuen Umsatz erstellen",
        "edit_heading" => "Umsatz bearbeiten",
        "delete_heading" => "Umsatz löschen",
        "bulk_heading" => "Ausgewählte Umsätze löschen",

        "table" => [
            'date' => 'Datum',
            'account' => 'Konto',
            'amount' => 'Betrag',
            'amount_in' => 'Betrag in ',
            'destination' => 'Ziel',
            'category' => 'Kategorie',
            'group' => 'Gruppe',
            'notes' => 'Notizen',
            'empty' => 'Keine Umsätze gefunden',
        ],

        "form" => [
            'category_placeholder' => 'Kategorie wählen',
            'account_placeholder' => 'Konto wählen',
        ],

        "filter" => [
            'all' => 'Alle',
            'expenses' => 'Alle Ausgaben',
            'fix_expenses' => 'Fixe Ausgaben',
            'var_expenses' => 'Variable Ausgaben',
            'revenues' => 'Einnahmen',
            'fix_revenues' => 'Fixe Einnahmen',
            'var_revenues' => 'Variable Einnahmen',
        ]
    ],

    "users" => [
        "navigation_label" => "Benutzer",
        "create_label" => "Neuer Benutzer",
        "create_heading" => "Neuen Benutzer erstellen",
        "edit_heading" => "Benutzer bearbeiten",
        "delete_heading" => "Benutzer löschen",
        "password" => "Passwort",
        "password_confirmation" => "Passwort wiederholen",
        "user_or_email" => "Benutzername oder E-Mail",

        "table" => [
            'first_name' => 'Vorname',
            'last_name' => 'Nachname',
            'email' => 'E-Mail',
            'name' => 'Benutzername',
            'is_admin' => 'Administrator',
            'empty' => 'Keine Benutzer gefunden'
        ],

        "filter" => [
            'all' => 'Alle',
            'admins' => 'Administratoren',
            'users' => 'Benutzer',
        ]
    ]

];
