<?php

return [

    'slug' => 'benutzer',
    'navigation_label' => 'Benutzer',
    'profile-slug' => 'profil',
    'unverified_message' => 'Bitte das Passwort ändern',

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
        'name_unique_warning' => 'Benutzername bereits vergeben',
        'email' => 'E-Mail',
        'email_unique_warning' => 'E-Mail bereits vergeben',
        'verified' => 'Verifiziert',
        'is_admin' => 'Administrator',
    ],

    'filter' => [
        'verified' => 'Verifiziert',
        'unverified' => 'Unverifiziert',
    ],

    'login' => [
        'user_or_email' => 'Benutzername oder E-Mail',
    ],

    'empty' => 'Keine Benutzer gefunden'

];
