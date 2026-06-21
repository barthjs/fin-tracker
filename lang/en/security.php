<?php

declare(strict_types=1);

return [

    'label' => 'security',
    'plural_label' => 'securities',

    'fields' => [
        'isin' => 'ISIN',
        'symbol' => 'Symbol',
        'total_quantity' => 'Quantity',
        'region' => 'Region',
        'sector' => 'Sector',
        'country' => 'Country',
        'exchange' => 'Exchange',
        'ter' => 'TER (%)',
        'website' => 'Website',
    ],

    'type' => [
        'bond' => 'Bond',
        'derivative' => 'Derivative',
        'etf' => 'ETF',
        'fund' => 'Fund',
        'stock' => 'Stock',
    ],

    'region' => [
        'global' => 'Global',
        'north_america' => 'North America',
        'latin_america' => 'Latin America',
        'europe' => 'Europe',
        'asia_pacific' => 'Asia Pacific',
        'middle_east_africa' => 'Middle East & Africa',
    ],

    'sector' => [
        'energy' => 'Energy',
        'materials' => 'Materials',
        'industrials' => 'Industrials',
        'consumer_discretionary' => 'Consumer Discretionary',
        'consumer_staples' => 'Consumer Staples',
        'health_care' => 'Health Care',
        'financials' => 'Financials',
        'information_technology' => 'Information Technology',
        'communication_services' => 'Communication Services',
        'utilities' => 'Utilities',
        'real_estate' => 'Real Estate',
    ],

    'unassigned' => 'Unassigned',

    'sections' => [
        'general' => 'General',
        'classification' => 'Classification',
        'appearance' => 'Appearance',
    ],

    'buttons' => [
        'bulk_edit_classification' => 'Edit classification',
        'bulk_edit_region' => 'Edit region',
        'bulk_edit_sector' => 'Edit sector',
        'bulk_edit_country' => 'Edit country',
    ],

    'import' => [
        'modal_heading' => 'Import Securities',
        'failure_heading' => 'Security import failed',
        'success_heading' => 'Security import successful',
        'body_heading' => 'The security import has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully imported rows: ',

        'errors' => [
            'invalid_value' => 'The value ":value" is invalid.',
        ],

        'examples' => [
            'name' => [
                'SPDR S&P 500 ETF Trust',
                'Apple',
                'Microsoft',
            ],
            'isin' => [
                'US78462F1030',
                'US0378331005',
                'US5949181045',
            ],
            'type' => [
                'ETF',
                'Stock',
                'Stock',
            ],
            'symbol' => [
                'SPDR',
                'AAPL',
                'MSFT',
            ],
            'price' => [
                '100.00',
                '200.00',
                '300.00',
            ],
            'region' => [
                'North America',
                'North America',
                'North America',
            ],
            'sector' => [
                '',
                'Information Technology',
                'Information Technology',
            ],
            'country' => [
                'US',
                'US',
                'US',
            ],
            'exchange' => [
                'NYSE',
                'NASDAQ',
                'NASDAQ',
            ],
            'ter' => [
                '0.09',
                '',
                '',
            ],
            'website' => [
                'https://www.ssga.com',
                'https://www.apple.com',
                'https://www.microsoft.com',
            ],
        ],
    ],

    'export' => [
        'modal_heading' => 'Export Securities',
        'failure_heading' => 'Security export failed',
        'success_heading' => 'Security export successful',
        'body_heading' => 'The security export has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully exported rows: ',
        'file_name' => 'Securities_',
    ],

];
