<?php

namespace App\Enums;

enum TransactionGroup: string
{
    case VariableExpense = 'Variable Expense';
    case FixedExpense = 'Fixed Expense';
    case Income = 'Income';
    case Transfer = 'Transfer';
}
