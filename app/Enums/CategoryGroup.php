<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasOrderedCases;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CategoryGroup: string implements HasColor, HasLabel
{
    use HasOrderedCases;

    case FixExpenses = 'fix_expenses';
    case VarExpenses = 'var_expenses';
    case FixRevenues = 'fix_revenues';
    case VarRevenues = 'var_revenues';
    case Transfers = 'transfers';

    public function getColor(): string
    {
        return match ($this) {
            self::FixExpenses, self::VarExpenses => 'danger',
            self::FixRevenues, self::VarRevenues => 'success',
            self::Transfers => 'warning',
        };
    }

    public function getLabel(): string
    {
        return __('category.group.'.$this->value);
    }
}
