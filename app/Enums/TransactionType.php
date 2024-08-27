<?php

namespace App\Enums;

enum TransactionType
{
    case revenue;
    case expense;
    case transfer;
}
