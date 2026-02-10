<?php

declare(strict_types=1);

return [

    'label' => 'Subscription',
    'plural_label' => 'Subscriptions',

    'fields' => [
        'period_unit' => 'Unit',
        'period_frequency' => 'Frequency',
        'day_of_month' => 'Day of Month',
        'started_at' => 'Start Date',
        'next_payment_date' => 'Next Payment Date',
        'due_until' => 'Due until',
        'ended_at' => 'End Date',
        'auto_generate_transaction' => 'Auto-generate transaction',
        'last_generated_at' => 'Last Generated',
        'last_generated_at_placeholder' => 'Never',
        'remind_before_payment' => 'Enable pre-reminder',
        'reminder_days_before' => 'Days lead time',
    ],

    'interval' => [
        'single' => [
            'day' => 'Daily',
            'week' => 'Weekly',
            'month' => 'Monthly',
            'year' => 'Yearly',
        ],
        'multiple' => 'Every :count :unit',
    ],

    'units' => [
        'day' => 'day|days',
        'week' => 'week|weeks',
        'month' => 'month|months',
        'year' => 'year|years',
    ],

    'hints' => [
        'next_payment' => 'Date of the next due execution.',
        'auto_generate' => 'If enabled, the system will automatically create a transaction on the due date.',
    ],

    'generated_note' => 'Automatically generated for subscription from :date.',

    'notifications' => [
        'generated_title' => 'Abonnement verarbeitet',
        'generated_body' => 'Die Zahlung für :name in Höhe von :amount wurde erstellt.',
        'catch_up_title' => ':count payments processed',
        'catch_up_body' => 'Successfully processed :count outstanding payments for :name (Total: :amount).',
        'failed_title' => 'Subscription failed',
        'failed_body' => 'The automatic booking for ":name" could not be created.',
        'reminder_title' => 'Payment Reminder: :name',
        'reminder_body' => 'The next payment for :name (:amount) is due on :date.',
    ],

    'import' => [
        'modal_heading' => 'Import Subscriptions',
        'failure_heading' => 'Subscription import failed',
        'success_heading' => 'Subscription import successful',
        'body_heading' => 'The subscription import has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully imported rows: ',
    ],

    'export' => [
        'modal_heading' => 'Export Subscriptions',
        'failure_heading' => 'Subscription export failed',
        'success_heading' => 'Subscription export successful',
        'body_heading' => 'The subscription export has been completed.',
        'body_failure' => 'Failed rows: ',
        'body_success' => 'Successfully exported rows: ',
        'file_name' => 'Subscriptions_',
    ],

    'stats' => [
        'monthly_cost' => 'Monthly Cost',
        'avg_per_sub' => 'Ø :amount per subscription',
        'yearly_cost' => 'Yearly Cost',
        'amount_due_this_month' => 'Amount Due This Month',
    ],

];
