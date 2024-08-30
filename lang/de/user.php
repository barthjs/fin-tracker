<?php

return [

    'url' => 'benutzer',
    'navigation_label' => 'Benutzer',

    'buttons' => [
        'create_button_label' => 'Neuer Benutzer',
        'create_heading' => 'Neuen Benutzer erstellen',
        'edit_heading' => 'Benutzer bearbeiten',
        'delete_heading' => 'Benutzer löschen',
        'password' => 'Passwort',
        'password_confirmation' => 'Passwort wiederholen',
    ],

    'columns' => [
        'first_name' => 'Vorname',
        'last_name' => 'Nachname',
        'name' => 'Benutzername',
        'email' => 'E-Mail',
        'verified' => 'Verifiziert',
        'is_admin' => 'Administrator',
    ],

    'filter' => [
        'all' => 'Alle',
        'verified' => 'Verifiziert',
        'admins' => 'Administratoren',
        'users' => 'Benutzer',
    ],

    'login' => [
        'user_or_email' => 'Benutzername oder E-Mail',
    ],

    'empty' => 'Keine Benutzer gefunden'

];
