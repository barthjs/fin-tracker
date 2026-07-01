<?php

declare(strict_types=1);

return [

    'label' => 'Budget',
    'plural_label' => 'Budgets',

    'fields' => [
        'amount' => 'Budget',
        'period_unit' => 'Unit',
        'period_frequency' => 'Frequency',
        'starts_at' => 'Start Date',
        'ends_at' => 'End Date',
        'notify_on_exceed' => 'Send notifications',
        'warning_threshold' => 'Warning threshold',
        'warning_threshold_helper' => 'Optional. From this share on, the bar turns yellow and a warning is sent. At 100 % the budget counts as exceeded.',
        'warning_threshold_none' => 'None',
        'spent' => 'Spent',
        'remaining' => 'Remaining',
        'usage' => 'Usage',
    ],

    'sections' => [
        'current_period' => 'Current period',
    ],

    'validation' => [
        'category_expense' => 'A budget requires an expense category.',
        'category_unique' => 'This category already has a budget.',
    ],

    'notifications' => [
        'warning_title' => 'Budget almost used up: :category',
        'warning_body' => 'The budget for :category has reached :percentage% (:spent of :budgeted).',
        'exceeded_title' => 'Budget exceeded: :category',
        'exceeded_body' => 'The budget for :category is exceeded, :percentage% used (:spent of :budgeted).',
    ],

    'import' => [
        'modal_heading' => 'Import Budgets',
        'failure_heading' => 'Budget import failed',
        'success_heading' => 'Budget import successful',
        'body_heading' => 'The budget import has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully imported rows: ',
    ],

    'export' => [
        'modal_heading' => 'Export Budgets',
        'failure_heading' => 'Budget export failed',
        'success_heading' => 'Budget export successful',
        'body_heading' => 'The budget export has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully exported rows: ',
        'file_name' => 'Budgets_',
    ],

];
