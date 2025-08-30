<?php

declare(strict_types=1);

return [

    'label' => 'Benutzer',
    'plural_label' => 'Benutzer',
    'slug' => 'benutzer',
    'profile-slug' => 'profil',

    'fields' => [
        'first_name' => 'Vorname',
        'last_name' => 'Nachname',
        'full_name' => 'Name',
        'username' => 'Benutzername',
        'username_or_email' => 'Benutzername oder E-Mail',
        'avatar' => 'Profilbild',
        'is_verified' => 'Verifiziert',
        'is_admin' => 'Administrator',
    ],

    'change_password_message' => 'Bitte das Password ändern, um fortzufahren.',

    'sessions' => [
        'heading' => 'Geräte & Sitzungen',
        'delete' => 'Andere Browser-Sitzungen abmelden',
        'unknown_platform' => 'Unbekannte Plattform',
        'unknown_browser' => 'Unbekannter Browser',
        'this_device' => 'Dieses Gerät',
        'last_active' => 'Zuletzt aktiv',
        'logout_success' => 'Alle anderen Browser-Sitzungen wurden erfolgreich abgemeldet.',
    ],

];
