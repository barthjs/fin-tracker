<?php

return [

    'modal' => [

        'description' => <<<'BLADE'
            Du benötigst eine App wie Google Authenticator (<x-filament::link href="https://itunes.apple.com/us/app/google-authenticator/id388497605" target="_blank">iOS</x-filament::link>, <x-filament::link href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Android</x-filament::link>), um diesen Vorgang abzuschließen.
            BLADE,

        'content' => [

            'qr_code' => [

                'instruction' => 'Scanne diesen QR-Code mit deiner Authenticator-App:',

            ],

            'text_code' => [

                'instruction' => 'Oder gib diesen Code manuell ein:',

            ],

            'recovery_codes' => [

                'instruction' => 'Bitte speicher die folgenden Wiederherstellungscodes an einem sicheren Ort. Sie werden nur einmal angezeigt, aber du benötigst sie, wenn du den Zugang zu deiner Authenticator-App verlierst:',

            ],

        ],

        'form' => [

            'code' => [

                'label' => 'Gib den 6-stelligen Code aus der Authenticator-App ein',

                'below_content' => 'Du musst bei jeder Anmeldung oder beim Ausführen sensibler Aktionen den 6-stelligen Code aus deiner Authenticator-App eingeben.',

            ],

        ],

    ],

];
