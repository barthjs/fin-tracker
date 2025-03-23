<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TransactionGroup: string implements HasLabel
{
    public function getLabel(): ?string
    {
        return $this->name;
    }

    case fix_expenses = 'fix_expenses';
    case var_expenses = 'var_expenses';
    case fix_revenues = 'fix_revenues';
    case var_revenues = 'var_revenues';
    case transfers = 'transfers';
}
