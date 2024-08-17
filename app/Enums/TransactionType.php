<?php

namespace App\Enums;

enum TransactionType: string {
    case Revenue = 'Revenue';
    case Expense = 'Expense';
    case Transfer = 'Transfer';
}
