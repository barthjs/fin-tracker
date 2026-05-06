<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Filament\Resources\CategoryStatistics\Widgets\CategoryStatisticChart;
use App\Filament\Resources\Securities\Widgets\SecurityChart;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionsByAccountChart;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionsByCategoryChart;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionStats;
use App\Filament\Widgets\AccountChart;
use App\Filament\Widgets\ExpenseChart;
use App\Filament\Widgets\PortfolioChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TotalOverviewTable;
use App\Models\Account;
use App\Models\Category;
use App\Models\CategoryStatistic;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Subscription;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

function seedFinancialData(): void
{
    $account = Account::factory()->create(['balance' => 100.0, 'is_active' => true]);
    Portfolio::factory()->create(['market_value' => 50.0, 'is_active' => true]);
    Security::factory()->create(['is_active' => true]);

    $expenseCategory = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    $revenueCategory = Category::factory()->create(['group' => CategoryGroup::VarRevenues]);
    CategoryStatistic::factory()->create(['category_id' => $expenseCategory->id, 'year' => now()->year]);
    CategoryStatistic::factory()->create(['category_id' => $revenueCategory->id, 'year' => now()->year]);

    Subscription::factory()->count(2)->create([
        'account_id' => $account->id,
        'category_id' => $expenseCategory->id,
        'next_payment_date' => today()->addDays(5),
    ]);
}

it('renders the dashboard widgets', function (string $widget): void {
    seedFinancialData();

    livewire($widget)->assertOk();
})->with([
    StatsOverview::class,
    AccountChart::class,
    PortfolioChart::class,
    ExpenseChart::class,
    RevenueChart::class,
    TotalOverviewTable::class,
]);

it('renders the security chart', function (): void {
    seedFinancialData();

    livewire(SecurityChart::class)->assertOk();
});

it('renders the subscription widgets', function (string $widget): void {
    seedFinancialData();

    livewire($widget)->assertOk();
})->with([
    SubscriptionStats::class,
    SubscriptionsByAccountChart::class,
    SubscriptionsByCategoryChart::class,
]);

it('renders the category statistic chart', function (): void {
    seedFinancialData();

    livewire(CategoryStatisticChart::class)->assertOk();
});
