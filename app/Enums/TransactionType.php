<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasColor, HasLabel
{
    case Expense = 'expense';
    case Revenue = 'revenue';
    case Transfer = 'transfer';

    public function getLabel(): string
    {
        return __('transaction.type.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Expense => 'danger',
            self::Revenue => 'success',
            self::Transfer => 'warning',
        };
    }
}
