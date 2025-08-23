<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasLabel
{
    case Expense = 'expense';
    case Revenue = 'revenue';
    case Transfer = 'transfer';

    public function getLabel(): string
    {
        return __('transaction.type.'.$this->value);
    }
}
