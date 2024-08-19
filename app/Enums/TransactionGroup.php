<?php

namespace App\Enums;

enum TransactionGroup
{
    case var_expense;
    case fix_expense;
    case income;
    case transfer;
}
