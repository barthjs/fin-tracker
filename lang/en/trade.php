<?php

declare(strict_types=1);

return [

    'label' => 'Trade',
    'plural_label' => 'Trades',

    'fields' => [
        'total_amount' => 'Total amount',
        'quantity' => 'Quantity',
        'tax' => 'Tax',
        'fee' => 'Fee',
    ],

    'type' => [
        'buy' => 'Buy',
        'sell' => 'Sell',
    ],

    'import' => [
        'modal_heading' => 'Import trades',
        'failure_heading' => 'Trade import failed',
        'success_heading' => 'Trade import successful',
        'body_heading' => 'The trade import has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully imported rows: ',
    ],

    'export' => [
        'modal_heading' => 'Export trades',
        'failure_heading' => 'Trade export failed',
        'success_heading' => 'Trade export successful',
        'body_heading' => 'The trade export has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully exported rows: ',
        'file_name' => 'Trades_',
    ],

];
