<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryStatistic;
use App\Models\Transaction;
use App\Services\StatisticService;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

function statisticService(): StatisticService
{
    return resolve(StatisticService::class);
}

it('updates category statistics for a guest using the owner timezone', function (): void {
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    $account = Account::factory()->create();
    Transaction::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 100.0,
        'date_time' => now(),
    ]);

    auth()->logout();

    statisticService()->updateCategoryStatistics($category->id, now());

    assertDatabaseHas('category_statistics', ['category_id' => $category->id]);
});

it('does nothing for a guest when the category is missing', function (): void {
    auth()->logout();

    statisticService()->updateCategoryStatistics('missing-category-id', now());

    expect(CategoryStatistic::query()->withoutGlobalScopes()->count())->toBe(0);
});

it('deletes a statistic when its yearly sum drops to zero', function (): void {
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    statisticService()->updateCategoryStatistics($category->id, now());

    expect(CategoryStatistic::query()->where('category_id', $category->id)->exists())->toBeFalse();
});
