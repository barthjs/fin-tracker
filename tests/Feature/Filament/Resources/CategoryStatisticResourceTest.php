<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Filament\Resources\CategoryStatistics\Pages\ListCategoryStatistics;
use App\Models\Category;
use App\Models\CategoryStatistic;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders with the current year in the filter when there are no statistics', function (): void {
    livewire(ListCategoryStatistics::class)
        ->loadTable()
        ->assertOk();
});

it('lists statistics scoped to the user and filters by year and type', function (): void {
    $expenseCategory = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    $revenueCategory = Category::factory()->create(['group' => CategoryGroup::VarRevenues]);

    $currentExpense = CategoryStatistic::factory()->create(['category_id' => $expenseCategory->id, 'year' => now()->year]);
    $currentRevenue = CategoryStatistic::factory()->create(['category_id' => $revenueCategory->id, 'year' => now()->year]);
    $past = CategoryStatistic::factory()->create(['category_id' => $expenseCategory->id, 'year' => now()->year - 1]);

    $other = User::factory()->create();
    $otherCategory = Category::factory()->create(['user_id' => $other->id]);
    $otherStatistic = CategoryStatistic::factory()->create(['category_id' => $otherCategory->id, 'year' => now()->year]);

    livewire(ListCategoryStatistics::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$currentExpense, $currentRevenue])
        ->assertCanNotSeeTableRecords([$past, $otherStatistic])
        ->filterTable('year', now()->year - 1)
        ->assertCanSeeTableRecords([$past])
        ->assertCanNotSeeTableRecords([$currentExpense])
        ->resetTableFilters()
        ->set('activeTab', TransactionType::Expense->value)
        ->assertCanSeeTableRecords([$currentExpense])
        ->assertCanNotSeeTableRecords([$currentRevenue])
        ->set('activeTab', TransactionType::Revenue->value)
        ->assertCanSeeTableRecords([$currentRevenue])
        ->assertCanNotSeeTableRecords([$currentExpense]);
});
