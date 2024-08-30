<?php

return [

    "bank_account_transactions" => [
        "navigation_label" => "Bank account transactions",
        "create_label" => "New transaction",
        "create_heading" => "Create new transaction",
        "edit_heading" => "Edit transaction",
        "delete_heading" => "Delete transaction",
        "bulk_heading" => "Delete selected transactions",

        "table" => [
            'date' => 'Date',
            'account' => 'Bank account',
            'amount' => 'Amount',
            'amount_in' => 'Amount in ',
            'destination' => 'Destination',
            'category' => 'Category',
            'group' => 'Group',
            'notes' => 'Notes',
            'empty' => 'No transactions found',
        ],

        "form" => [
            'category_placeholder' => 'Choose category',
            'account_placeholder' => 'Choose bank account',
        ],

        "filter" => [
            'all' => 'All',
            'expenses' => 'All expenses',
            'fix_expenses' => 'Fixed expenses',
            'var_expenses' => 'Variable expenses',
            'revenues' => 'Revenues',
            'fix_revenues' => 'Fixed Revenues',
            'var_revenues' => 'Variable Revenues',
        ]
    ],

];
