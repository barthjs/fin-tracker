<?php

declare(strict_types=1);

return [

    'label' => 'Category',
    'plural_label' => 'Categories',
    'slug' => 'categories',

    'fields' => [
        'group' => 'Group',
    ],

    'group' => [
        'fix_expenses' => 'Fixed expenses',
        'var_expenses' => 'Variable expenses',
        'fix_revenues' => 'Fixed revenues',
        'var_revenues' => 'Variable revenues',
        'transfers' => 'Transfers',
    ],

    'buttons' => [
        'bulk_edit_group' => 'Edit group',
    ],

    'import' => [
        'modal_heading' => 'Import categories',
        'failure_heading' => 'Category import failed',
        'success_heading' => 'Category import successful',
        'body_heading' => 'The category import has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully imported rows: ',

        'examples' => [
            'name' => [
                'Rent',
                'Leisure',
                'Salary',
                'Interest',
                'Transfer',
            ],
            'group' => [
                'Fixed expenses',
                'Variable expenses',
                'Fixed revenues',
                'Variable revenues',
                'Transfers',
            ],
        ],
    ],

    'export' => [
        'modal_heading' => 'Export categories',
        'failure_heading' => 'Category export failed',
        'success_heading' => 'Category export successful',
        'body_heading' => 'The category export has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully exported rows: ',
        'file_name' => 'Categories_',
    ],

];
