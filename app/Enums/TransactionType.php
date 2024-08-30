<?php

namespace App\Enums;

enum TransactionType
{
    case expense;
    case revenue;
    case transfer;
}
