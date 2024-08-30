<?php

return [

    'url' => 'categories',
    'navigation_label' => 'Categories',

    'buttons' => [
        'create_button_label' => 'New category',
        'create_heading' => 'Create new category',
        'edit_heading' => 'Edit category',
        'delete_heading' => 'Delete category',
        'bulk_group' => 'Edit group',
        'export_heading' => 'Export categories',
        'import_heading' => 'Import categories',
    ],

    'columns' => [
        'name' => 'Name',
        'name_examples' => [
            'Rent',
            'Leisure',
            'Groceries',
            'Mobility',
            'Salary',
            'Interest',
            'Transfer'
        ],
        'group' => 'Group',
        'group_examples' => [
            'Fixed expenses',
            'Variable expenses',
            'Variable expenses',
            'Variable expenses',
            'Fixed revenues',
            'Variable revenues',
            'Transfers'
        ],
        'type' => 'Type',
    ],

    'form' => [
        'group_placeholder' => 'Select group',
    ],

    'types' => [
        'expense' => 'Expense',
        'revenue' => 'Revenue',
        'transfer' => 'Transfer'
    ],

    'groups' => [
        'fix_expenses' => 'Fixed expenses',
        'var_expenses' => 'Variable expenses',
        'fix_revenues' => 'Fixed revenues',
        'var_revenues' => 'Variable revenues',
        'transfers' => 'Transfers',
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Categories import failed',
            'success_heading' => 'Categories import successful',
            'body_heading' => 'The category import has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully imported rows: ',
        ],
        'export' => [
            'failure_heading' => 'Categories export failed',
            'success_heading' => 'Categories export successful',
            'body_heading' => 'The category export has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully exported rows: ',
            'file_name' => 'categories_'
        ]
    ],

    'empty' => 'No categories found'

];
