<?php

return [

    'url' => 'transactions',
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
        'account_placeholder' => 'Select bank account',
        'category_placeholder' => 'Select category',
    ],

    'filter' => [
        'all' => 'All',
        'expenses' => 'Expenses',
        'fix_expenses' => 'Fixed expenses',
        'var_expenses' => 'Variable expenses',
        'revenues' => 'Revenues',
        'fix_revenues' => 'Fixed revenues',
        'var_revenues' => 'Variable revenues',
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
            'file_name' => 'transactions_'
        ]
    ],

    'empty' => 'No transactions found',

];
