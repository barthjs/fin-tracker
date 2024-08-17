<?php

namespace App\Enums;

enum TransactionType: string {
    case Revenue = 'Revenue';
    case Expenses = 'Expenses';
    case Transfers = 'Transfers';
}
