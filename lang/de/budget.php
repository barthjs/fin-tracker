<?php

declare(strict_types=1);

return [

    'label' => 'Budget',
    'plural_label' => 'Budgets',

    'fields' => [
        'amount' => 'Budget',
        'period_unit' => 'Einheit',
        'period_frequency' => 'Frequenz',
        'starts_at' => 'Startdatum',
        'ends_at' => 'Enddatum',
        'notify_on_exceed' => 'Benachrichtigungen senden',
        'warning_threshold' => 'Warnschwelle',
        'warning_threshold_helper' => 'Optional. Ab diesem Anteil wird der Balken gelb und es kommt eine Warnung. Bei 100 % gilt das Budget als überschritten.',
        'warning_threshold_none' => 'Keine',
        'spent' => 'Ausgegeben',
        'remaining' => 'Verbleibend',
        'usage' => 'Auslastung',
    ],

    'sections' => [
        'current_period' => 'Aktueller Zeitraum',
    ],

    'validation' => [
        'category_expense' => 'Ein Budget ist nur für eine Ausgabenkategorie möglich.',
        'category_unique' => 'Für diese Kategorie existiert bereits ein Budget.',
    ],

    'notifications' => [
        'warning_title' => 'Budget fast aufgebraucht: :category',
        'warning_body' => 'Das Budget für :category hat :percentage% erreicht (:spent von :budgeted).',
        'exceeded_title' => 'Budget überschritten: :category',
        'exceeded_body' => 'Das Budget für :category ist überschritten, :percentage% verbraucht (:spent von :budgeted).',
    ],

    'import' => [
        'modal_heading' => 'Budgets importieren',
        'failure_heading' => 'Budgetimport fehlgeschlagen',
        'success_heading' => 'Budgetimport erfolgreich',
        'body_heading' => 'Der Budgetimport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreich importierte Zeilen: ',
    ],

    'export' => [
        'modal_heading' => 'Budgets exportieren',
        'failure_heading' => 'Budgetexport fehlgeschlagen',
        'success_heading' => 'Budgetexport erfolgreich',
        'body_heading' => 'Der Budgetexport wurde abgeschlossen.',
        'body_failure' => 'Fehlgeschlagene Zeilen: ',
        'body_success' => 'Erfolgreich exportierte Zeilen: ',
        'file_name' => 'Budgets_',
    ],

];
