<?php

declare(strict_types=1);

return [

    'slug' => 'transactions',
    'navigation_label' => 'Transactions',

    'buttons' => [
        'create_button_label' => 'New transaction',
        'create_heading' => 'Add new transaction',
        'edit_heading' => 'Edit transaction',
        'delete_heading' => 'Delete transaction',
        'bulk_delete_heading' => 'Delete selected transactions',
        'bulk_account' => 'Edit bank account',
        'bulk_category' => 'Edit category',
        'export_heading' => 'Export transactions',
        'import_heading' => 'Import transactions',
    ],

    'columns' => [
        'date' => 'Date',
        'amount' => 'Amount',
        'destination' => 'Destination',
        'notes' => 'Notes',
        'account' => 'Account',
        'category' => 'Category',
        'group' => 'Group',
        'type' => 'Type',
    ],

    'form' => [
        'account_placeholder' => 'Select account',
        'account_validation_message' => 'Please specify an account',
        'category_placeholder' => 'Select category',
        'category_validation_message' => 'Please specify a category',
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Transaction import failed',
            'success_heading' => 'Transaction import successful',
            'body_heading' => 'The transaction import has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully imported rows: ',
        ],
        'export' => [
            'failure_heading' => 'Transaction export failed',
            'success_heading' => 'Transaction export successful',
            'body_heading' => 'The transaction export has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully exported rows: ',
            'file_name' => 'transactions_',
        ],
    ],

    'empty' => 'No transactions found',

];
