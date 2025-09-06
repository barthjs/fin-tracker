<?php

declare(strict_types=1);

return [

    'label' => 'security',
    'plural_label' => 'securities',

    'fields' => [
        'isin' => 'ISIN',
        'symbol' => 'Symbol',
        'total_quantity' => 'Quantity',
    ],

    'type' => [
        'bond' => 'Bond',
        'derivative' => 'Derivative',
        'etf' => 'ETF',
        'fund' => 'Fund',
        'stock' => 'Stock',
    ],

    'import' => [
        'modal_heading' => 'Import Securities',
        'failure_heading' => 'Security import failed',
        'success_heading' => 'Security import successful',
        'body_heading' => 'The security import has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully imported rows: ',

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
