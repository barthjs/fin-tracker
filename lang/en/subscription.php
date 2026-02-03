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
    ],

];
