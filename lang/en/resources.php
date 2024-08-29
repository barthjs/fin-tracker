<?php

return [

    "transaction_categories" => [
        "navigation_label" => "Transaction categories",
        "create_label" => "New transaction category",
        "create_heading" => "Create new transaction category",
        "edit_heading" => "Edit transaction category",
        "delete_heading" => "Delete transaction category",

        "table" => [
            'name' => 'Name',
            'type' => 'Type',
            'group' => 'Group',
            'empty' => 'No categories found',
        ],

        "form" => [
            'type_placeholder' => 'Choose type',
            'group_placeholder' => 'Choose group',
        ],

        "types" => [
            'expense' => 'Expense',
            'revenue' => 'Revenue',
            'transfer' => 'Transfer'
        ],

        "groups" => [
            'fix_expenses' => 'Fixed expenses',
            'var_expenses' => 'Variable expenses',
            'fix_revenues' => 'Fixed revenues',
            'var_revenues' => 'Variable revenues',
            'transfers' => 'Transfers',
        ]
    ],

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

    "users" => [
        "navigation_label" => "Users",
        "create_label" => "New user",
        "create_heading" => "Create new user",
        "edit_heading" => "Edit user",
        "delete_heading" => "Delete user",
        "password" => "Password",
        "password_confirmation" => "Repeat password",
        "user_or_email" => "Username or email",

        "table" => [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'name' => 'Username',
            'is_admin' => 'Administrator',
            'empty' => 'No users found'
        ],

        "filter" => [
            'all' => 'All',
            'admins' => 'Administrators',
            'users' => 'Users',
        ]
    ]

];
