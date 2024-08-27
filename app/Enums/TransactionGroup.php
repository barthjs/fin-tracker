<?php

namespace App\Enums;

enum TransactionGroup
{
    case fix_expenses;
    case var_expenses;
    case fix_revenues;
    case var_revenues;
    case transfers;
}
