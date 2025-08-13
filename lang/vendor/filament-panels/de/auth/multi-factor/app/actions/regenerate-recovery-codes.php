<?php

declare(strict_types=1);

return [

    'modal' => [

        'description' => 'Wenn du deine Wiederherstellungscodes verlierst, kannst du sie hier neu generieren. Deine alten Wiederherstellungscodes werden sofort ungültig.',

        'form' => [

            'code' => [

                'label' => 'Gib den 6-stelligen Code aus der Authenticator-App ein',

            ],

            'password' => [

                'label' => 'Oder gib dein aktuelles Passwort ein',

            ],

        ],

    ],

    'show_new_recovery_codes' => [

        'modal' => [

            'heading' => 'Neue Wiederherstellungscodes',

            'description' => 'Bitte speicher die folgenden Wiederherstellungscodes an einem sicheren Ort. Sie werden nur einmal angezeigt, aber du benötigst sie, wenn du den Zugang zu deiner Authenticator-App verlierst:',

        ],

    ],

];
