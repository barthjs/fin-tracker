<?php

return [

    'slug' => 'benutzer',
    'navigation_label' => 'Benutzer',

    'buttons' => [
        'create_button_label' => 'Neuer Benutzer',
        'create_heading' => 'Neuen Benutzer erstellen',
        'edit_heading' => 'Benutzer bearbeiten',
        'delete_heading' => 'Benutzer löschen',
        'password' => 'Passwort',
        'password_confirmation' => 'Passwort wiederholen',
        'password_confirmation_warning' => 'Passwörter stimmen nicht überein.',
    ],

    'columns' => [
        'first_name' => 'Vorname',
        'last_name' => 'Nachname',
        'full_name' => 'Name',
        'name' => 'Benutzername',
        'email' => 'E-Mail',
        'verified' => 'Verifiziert',
        'is_admin' => 'Administrator',
    ],

    'filter' => [
        'verified' => 'Verifiziert',
        'unverified' => 'Unverifiziert',
        'admins' => 'Administratoren',
        'users' => 'Benutzer',
    ],

    'login' => [
        'user_or_email' => 'Benutzername oder E-Mail',
    ],

    'empty' => 'Keine Benutzer gefunden'

];
