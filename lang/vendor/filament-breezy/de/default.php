<?php

declare(strict_types=1);

return [

    'password_confirm' => [

        'description' => 'Bestätige bitte dein Passwort, um diese Aktion abzuschließen.',

    ],

    'two_factor' => [

        'description' => 'Bestätige bitte den Zugriff auf dein Konto, indem du den von deiner Authentifizierungs-App bereitgestellten Code eingibst.',
        'recovery' => [
            'description' => 'Bestätige bitte den Zugang zu deinem Konto, indem du einen deiner Wiederherstellungscodes eingibst.',
        ],
        'recovery_code_link' => 'Einen Wiederherstellungscode verwenden',

    ],

    'profile' => [

        'personal_info' => [
            'subheading' => 'Hier kannst du deine persönlichen Daten verwalten.',
        ],

        'password' => [
            'subheading' => 'Muss mindestens 8 Zeichen lang sein.',
        ],

        '2fa' => [
            'description' => 'Aktiviere hier die 2-Faktor-Authentifizierung für dein Konto (empfohlen).',
            'not_enabled' => [
                'title' => 'Du hast die Zwei-Faktor-Authentifizierung nicht aktiviert.',
                'description' => 'Wenn die Zwei-Faktor-Authentifizierung aktiviert ist, wirst du während der Anmeldung zur Eingabe eines sicheren, zufälligen Tokens aufgefordert. Du kannst dieses Token z.B. über die Google Authenticator-App auf deinem Handy abrufen.',
            ],
            'finish_enabling' => [
                'title' => 'Schließe die Aktivierung der Zwei-Faktor-Authentifizierung ab.',
                'description' => 'Um die Aktivierung abzuschließen, scanne den folgenden QR-Code mit deiner Authenticator-App oder gib den Einrichtungsschlüssel und den generierten OTP-Code ein.',
            ],
            'enabled' => [
                'title' => 'Du hast die Zwei-Faktor-Authentifizierung aktiviert!',
                'description' => 'Die Zwei-Faktor-Authentifizierung ist jetzt aktiv. Damit ist dein Konto noch besser geschützt.',
                'store_codes' => 'Speichere diese Wiederherstellungscodes sicher in einem Passwort-Manager. Sie helfen dir, wieder Zugriff auf dein Konto zu bekommen, falls du dein Authentifizierungsgerät verlierst.',
            ],
            'confirmation' => [
                'invalid_code' => 'Der eingegebene Code ist ungültig.',
            ],
        ],

    ],

];
