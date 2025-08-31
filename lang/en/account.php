<?php

declare(strict_types=1);

return [

    'label' => 'Account',
    'plural_label' => 'Accounts',

    'fields' => [
        'balance' => 'Balance',
        'transfer_account_id' => 'Target account',
    ],

    'buttons' => [
        'bulk_edit_account' => 'Edit account',
    ],

    'import' => [
        'modal_heading' => 'Import accounts',
        'failure_heading' => 'Account import failed',
        'success_heading' => 'Account import successful',
        'body_heading' => 'The account import has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully imported rows: ',

        'examples' => [
            'name' => [
                'Bank of America',
                'HSBC',
                'ING',
            ],
            'currency' => [
                'USD',
                'GBP',
                'EUR',
            ],
            'description' => [
                'Lorem ipsum dolor sit amet',
                'Lorem ipsum dolor sit amet',
                'Lorem ipsum dolor sit amet',
            ],
        ],
    ],

    'export' => [
        'modal_heading' => 'Export accounts',
        'failure_heading' => 'Account export failed',
        'success_heading' => 'Account export successful',
        'body_heading' => 'The account export has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully exported rows: ',
        'file_name' => 'Accounts_',
    ],

];
