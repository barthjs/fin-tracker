<?php

namespace App\Enums;

enum TransactionGroup: string
{
    case VarExp = 'VarExp';
    case FixExp = 'FixExp';
    case Inc = 'Inc';
    case Transfers = 'Transfers';
}
