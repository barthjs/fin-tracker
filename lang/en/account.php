<?php

declare(strict_types=1);

return [

    'label' => 'account',
    'plural_label' => 'accounts',

    'fields' => [
        'balance' => 'Balance',
        'transfer_account_id' => 'Target account',
        'notify_on_low_balance' => 'Notify on low balance',
        'low_balance_threshold' => 'Threshold',
    ],

    'buttons' => [
        'bulk_edit_account' => 'Edit account',
    ],

    'notifications' => [
        'low_balance_title' => 'Low balance: :name',
        'low_balance_body' => 'The account :name has fallen below :threshold (current balance: :balance).',
    ],

    'import' => [
        'modal_heading' => 'Import Accounts',
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
        'modal_heading' => 'Export Accounts',
        'failure_heading' => 'Account export failed',
        'success_heading' => 'Account export successful',
        'body_heading' => 'The account export has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully exported rows: ',
        'file_name' => 'Accounts_',
    ],

];
