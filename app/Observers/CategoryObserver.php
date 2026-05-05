<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Models\Category;

final class CategoryObserver
{
    public function creating(Category $category): void
    {
        $category->name = mb_trim($category->name);
        /** @phpstan-ignore-next-line */
        $category->type = $this->typeForGroup($category->group);

        /** @phpstan-ignore-next-line */
        if ($category->user_id === null) {
            $category->user_id = auth()->user()->id;
        }
    }

    public function updating(Category $category): void
    {
        $category->name = mb_trim($category->name);
        /** @phpstan-ignore-next-line */
        $category->type = $this->typeForGroup($category->group);
    }

    /**
     * Derive the transaction type from the category group.
     */
    private function typeForGroup(CategoryGroup $group): TransactionType
    {
        return match ($group) {
            CategoryGroup::FixExpenses, CategoryGroup::VarExpenses => TransactionType::Expense,
            CategoryGroup::FixRevenues, CategoryGroup::VarRevenues => TransactionType::Revenue,
            CategoryGroup::Transfers => TransactionType::Transfer,
        };
    }
}
