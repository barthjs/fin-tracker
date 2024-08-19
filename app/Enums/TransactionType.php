<?php

namespace App\Enums;

enum TransactionType
{
    case income;
    case expense;
    case transfer;
}
