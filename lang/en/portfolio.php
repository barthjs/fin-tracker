<?php

declare(strict_types=1);

return [

    'slug' => 'portfolio',
    'navigation_label' => 'Portfolios',

    'buttons' => [
        'create_button_label' => 'New portfolio',
        'create_heading' => 'Create new portfolio',
        'edit_heading' => 'Edit portfolio',
        'delete_heading' => 'Delete portfolio',
        'export_heading' => 'Export portfolios',
        'import_heading' => 'Import portfolios',
    ],

    'columns' => [
        'logo' => 'Logo',
        'name' => 'Name',
        'name_examples' => [
            'Bank of America',
            'ING',
            'HSBC',
        ],
        'market_value' => 'Volume',
        'description' => 'Description',
        'description_examples' => [
            'Lorem ipsum dolor sit amet',
            'Lorem ipsum dolor sit amet',
            'Lorem ipsum dolor sit amet',
        ],
    ],

    'notifications' => [
        'import' => [
            'failure_heading' => 'Portfolio import failed',
            'success_heading' => 'Portfolio import successful',
            'body_heading' => 'The portfolio import has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully imported rows: ',
        ],
        'export' => [
            'failure_heading' => 'Portfolio export failed',
            'success_heading' => 'Portfolio export successful',
            'body_heading' => 'The portfolio export has been completed.',
            'body_failure' => 'Failed rows: ',
            'body_success' => 'Successfully exported rows: ',
            'file_name' => 'portfolios_',
        ],
    ],

    'empty' => 'No portfolios found',

];
