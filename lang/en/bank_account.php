<?php

return [

    'url' => 'bank-accounts',
    'navigation_label' => 'Bank accounts',

    'buttons' => [
        'create_button_label' => 'New bank account',
        'create_heading' => 'Create new bank account',
        'edit_heading' => 'Edit bank account',
        'delete_heading' => 'Delete bank account',
        'bulk_currency' => 'Change currency',
        'export_heading' => 'Export bank accounts',
        'import_heading' => 'Import bank accounts',
    ],

    'columns' => [
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
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Bank account import failed',
            'success_heading' => 'Bank account import successful',
            'body_heading' => 'The bank account import has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully imported rows: ',
        ],
        'export' => [
            'failure_heading' => 'Bank account failed',
            'success_heading' => 'Bank account successful',
            'body_heading' => 'The bank account export has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully exported rows: ',
            'file_name' => 'bank_accounts_'
        ]
    ],

    'empty' => 'No bank accounts found'
];
