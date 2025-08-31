<?php

declare(strict_types=1);

return [

    'label' => 'Security',
    'plural_label' => 'Securities',

    'fields' => [
        'isin' => 'ISIN',
        'symbol' => 'Symbol',
        'total_quantity' => 'Quantity',
    ],

    'type' => [
        'bond' => 'Bond',
        'derivative' => 'Derivative',
        'etf' => 'ETF',
        'fund' => 'Fund',
        'stock' => 'Stock',
    ],

    'import' => [
        'modal_heading' => 'Import securities',
        'failure_heading' => 'Security import failed',
        'success_heading' => 'Security import successful',
        'body_heading' => 'The security import has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully imported rows: ',
    ],

    'export' => [
        'modal_heading' => 'Export securities',
        'failure_heading' => 'Security export failed',
        'success_heading' => 'Security export successful',
        'body_heading' => 'The security export has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully exported rows: ',
        'file_name' => 'Securities_',
    ],

];
