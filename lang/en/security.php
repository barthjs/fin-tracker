<?php

return [

    'slug' => 'securities',
    'navigation_label' => 'Securities',

    'buttons' => [
        'create_button_label' => 'New security',
        'create_heading' => 'Create new security',
        'edit_heading' => 'Edit security',
        'delete_heading' => 'Delete security',
        'export_heading' => 'Export Securities',
        'import_heading' => 'Import Securities',
    ],

    'columns' => [
        'logo' => 'Logo',
        'name' => 'Name',
        'isin' => 'ISIN',
        'symbol' => 'Symbol',
        'price' => 'Price',
        'total_quantity' => 'Quantity',
        'description' => 'Description',
        'type' => 'Type',
    ],

    'form' => [
        'type_placeholder' => 'Select Type',
    ],

    'types' => [
        'BOND' => 'Bond',
        'DERIVATIVE' => 'Derivative',
        'ETF' => 'ETF',
        'FUND' => 'Fund',
        'STOCK' => 'Stock',
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Security import failed',
            'success_heading' => 'Security import successful',
            'body_heading' => 'The security import has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully imported rows: ',
        ],
        'export' => [
            'failure_heading' => 'Security export failed',
            'success_heading' => 'Security export successful',
            'body_heading' => 'The security export has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully exported rows: ',
            'file_name' => 'securities_'
        ]
    ],

    'empty' => 'No securities found'

];
