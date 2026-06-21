<?php

declare(strict_types=1);

return [

    'label' => 'Wertpapier',
    'plural_label' => 'Wertpapiere',

    'fields' => [
        'isin' => 'ISIN',
        'symbol' => 'Symbol',
        'total_quantity' => 'Anzahl',
        'region' => 'Region',
        'sector' => 'Sektor',
        'country' => 'Land',
        'exchange' => 'Börsenplatz',
        'ter' => 'TER (%)',
        'website' => 'Website',
    ],

    'type' => [
        'bond' => 'Anleihe',
        'derivative' => 'Derivat',
        'etf' => 'ETF',
        'fund' => 'Fonds',
        'stock' => 'Aktie',
    ],

    'region' => [
        'global' => 'Global',
        'north_america' => 'Nordamerika',
        'latin_america' => 'Lateinamerika',
        'europe' => 'Europa',
        'asia_pacific' => 'Asien-Pazifik',
        'middle_east_africa' => 'Naher Osten & Afrika',
    ],

    'sector' => [
        'energy' => 'Energie',
        'materials' => 'Grundstoffe',
        'industrials' => 'Industrie',
        'consumer_discretionary' => 'Zyklischer Konsum',
        'consumer_staples' => 'Basiskonsumgüter',
        'health_care' => 'Gesundheitswesen',
        'financials' => 'Finanzwesen',
        'information_technology' => 'Informationstechnologie',
        'communication_services' => 'Kommunikationsdienste',
        'utilities' => 'Versorger',
        'real_estate' => 'Immobilien',
    ],

    'unassigned' => 'Nicht zugeordnet',

    'sections' => [
        'general' => 'Stammdaten',
        'classification' => 'Klassifizierung',
        'appearance' => 'Darstellung',
    ],

    'buttons' => [
        'bulk_edit_classification' => 'Klassifizierung bearbeiten',
        'bulk_edit_region' => 'Region bearbeiten',
        'bulk_edit_sector' => 'Sektor bearbeiten',
        'bulk_edit_country' => 'Land bearbeiten',
    ],

    'import' => [
        'modal_heading' => 'Wertpapiere importieren',
        'failure_heading' => 'Wertpapierimport fehlgeschlagen',
        'success_heading' => 'Wertpapierimport erfolgreich',
        'body_heading' => 'Der Wertpapierimport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreich importierte Zeilen: ',

        'errors' => [
            'invalid_value' => 'Der Wert „:value“ ist ungültig.',
        ],

        'examples' => [
            'name' => [
                'Apple',
                'Microsoft',
                'SPDR S&P 500 ETF Trust',
            ],
            'isin' => [
                'US0378331005',
                'US5949181045',
                'US78462F1030',
            ],
            'type' => [
                'Aktie',
                'Aktie',
                'ETF',
            ],
            'symbol' => [
                'AAPL',
                'MSFT',
                'SPDR',
            ],
            'price' => [
                '100.00',
                '200.00',
                '300.00',
            ],
            'region' => [
                'Nordamerika',
                'Nordamerika',
                'Nordamerika',
            ],
            'sector' => [
                'Informationstechnologie',
                'Informationstechnologie',
                '',
            ],
            'country' => [
                'US',
                'US',
                'US',
            ],
            'exchange' => [
                'NASDAQ',
                'NASDAQ',
                'NYSE',
            ],
            'ter' => [
                '',
                '',
                '0.09',
            ],
            'website' => [
                'https://www.apple.com',
                'https://www.microsoft.com',
                'https://www.ssga.com',
            ],
        ],
    ],

    'export' => [
        'modal_heading' => 'Wertpapier exportieren',
        'failure_heading' => 'Wertpapierexport fehlgeschlagen',
        'success_heading' => 'Wertpapierexport erfolgreich',
        'body_heading' => 'Der Wertpapierexport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreiche exportierte Zeilen: ',
        'file_name' => 'Wertpapiere_',
    ],

];
