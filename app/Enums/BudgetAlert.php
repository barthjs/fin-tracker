<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Stage of a budget notification within one period.
 */
enum BudgetAlert: string
{
    case Warning = 'warning';
    case Exceeded = 'exceeded';
}
