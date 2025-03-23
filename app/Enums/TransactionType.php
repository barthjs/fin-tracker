<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionType
{
    case expense;
    case revenue;
    case transfer;
}
