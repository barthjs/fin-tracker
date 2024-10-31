<?php

return [

    'slug' => 'accounts',
    'navigation_label' => 'Accounts',

    'buttons' => [
        'create_button_label' => 'New account',
        'create_heading' => 'Create new account',
        'edit_heading' => 'Edit account',
        'delete_heading' => 'Delete account',
        'bulk_currency' => 'Edit currency',
        'export_heading' => 'Export accounts',
        'import_heading' => 'Import accounts',
    ],

    'columns' => [
        'logo' => 'Logo',
        'name' => 'Name',
        'name_examples' => [
            'Bank of America',
            'ING',
            'HSBC'
        ],
        'balance' => 'Balance',
        'currency' => 'Currency',
        'currency_examples' => [
            'EUR',
            'GBP',
            'USD'
        ],
        'description' => 'Description',
        'description_examples' => [
            'Lorem ipsum dolor sit amet',
            'Lorem ipsum dolor sit amet',
            'Lorem ipsum dolor sit amet'
        ]
    ],

    'form' => [
        'currency_placeholder' => 'Choose currency',
        'currency_validation_message' => 'Please specify a currency',
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Account import failed',
            'success_heading' => 'Account import successful',
            'body_heading' => 'The account import has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully imported rows: ',
        ],
        'export' => [
            'failure_heading' => 'Account failed',
            'success_heading' => 'Account successful',
            'body_heading' => 'The account export has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully exported rows: ',
            'file_name' => 'accounts_'
        ]
    ],

    'empty' => 'No accounts found'

];
