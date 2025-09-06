<?php

declare(strict_types=1);

return [

    'label' => 'transaction',
    'plural_label' => 'transactions',

    'fields' => [
        'amount' => 'Amount',
        'payee' => 'Payee',
    ],

    'type' => [
        'expense' => 'Expense',
        'revenue' => 'Revenue',
        'transfer' => 'Transfer',
    ],

    'import' => [
        'modal_heading' => 'Import Transactions',
        'failure_heading' => 'Transaction import failed',
        'success_heading' => 'Transaction import successful',
        'body_heading' => 'The transaction import has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully imported rows: ',
    ],

    'export' => [
        'modal_heading' => 'Export Transactions',
        'failure_heading' => 'Transaction export failed',
        'success_heading' => 'Transaction export successful',
        'body_heading' => 'The transaction export has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully exported rows: ',
        'file_name' => 'Transactions_',
    ],

];
