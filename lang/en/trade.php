<?php

declare(strict_types=1);

return [

    'slug' => 'trades',
    'navigation_label' => 'Trades',

    'buttons' => [
        'create_button_label' => 'New trade',
        'create_heading' => 'Create new trade',
        'edit_heading' => 'Edit trade',
        'delete_heading' => 'Delete trade',
        'bulk_delete_heading' => 'Delete selected trades',
        'bulk_account' => 'Edit account',
        'bulk_portfolio' => 'Edit portfolio',
        'bulk_security' => 'Edit security',
        'export_heading' => 'Export trades',
        'import_heading' => 'Import trades',
    ],

    'columns' => [
        'date' => 'Date',
        'total_amount' => 'Total amount',
        'quantity' => 'Quantity',
        'price' => 'Price',
        'tax' => 'Tax',
        'fee' => 'Fee',
        'type' => 'Type',
        'notes' => 'Notes',
        'account' => 'Account',
        'portfolio' => 'Portfolio',
        'security' => 'Security',
    ],

    'form' => [
        'type_placeholder' => 'Select type',
        'account_placeholder' => 'Select account',
        'account_validation_message' => 'Please specify an account',
        'portfolio_placeholder' => 'Select portfolio',
        'portfolio_validation_message' => 'Please specify a portfolio',
        'security_placeholder' => 'Select security',
        'security_validation_message' => 'Please specify a security',
    ],

    'types' => [
        'BUY' => 'Buy',
        'SELL' => 'Sell',
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Trade import failed',
            'success_heading' => 'Trade import successful',
            'body_heading' => 'The trade import has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully imported rows: ',
        ],
        'export' => [
            'failure_heading' => 'Trade export failed',
            'success_heading' => 'Trade export successful',
            'body_heading' => 'The trade export has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully exported rows: ',
            'file_name' => 'trades_',
        ],
    ],

    'empty' => 'No trades found',

];
