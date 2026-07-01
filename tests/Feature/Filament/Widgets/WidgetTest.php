<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\Country;
use App\Enums\SecurityRegion;
use App\Enums\SecuritySector;
use App\Enums\SecurityType;
use App\Filament\Resources\CategoryStatistics\Widgets\CategoryStatisticChart;
use App\Filament\Resources\Securities\Widgets\SecurityChart;
use App\Filament\Resources\Securities\Widgets\SecurityRegionChart;
use App\Filament\Resources\Securities\Widgets\SecuritySectorChart;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionsByAccountChart;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionsByCategoryChart;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionStats;
use App\Filament\Widgets\AccountChart;
use App\Filament\Widgets\BudgetOverviewWidget;
use App\Filament\Widgets\ExpenseChart;
use App\Filament\Widgets\PortfolioChart;
use App\Filament\Widgets\RevenueChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TotalOverviewTable;
use App\Models\Account;
use App\Models\Budget;
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

it('renders the security widgets', function (string $widget): void {
    seedFinancialData();

    livewire($widget)->assertOk();
})->with([
    SecurityChart::class,
    SecurityRegionChart::class,
    SecuritySectorChart::class,
]);

it('applies the table filters to the security widgets', function (): void {
    $matching = Security::factory()->create([
        'type' => SecurityType::Stock,
        'region' => SecurityRegion::Europe,
        'sector' => SecuritySector::Financials,
        'country' => Country::DE,
        'is_active' => false,
    ]);
    Security::factory()->create(['region' => SecurityRegion::AsiaPacific, 'is_active' => false]);

    livewire(SecurityRegionChart::class, [
        'tableFilters' => [
            'type' => ['values' => [$matching->type->value]],
            'region' => ['values' => [SecurityRegion::Europe->value]],
            'sector' => ['values' => [SecuritySector::Financials->value]],
            'country' => ['values' => [Country::DE->value]],
            'inactive' => ['isActive' => true],
        ],
    ])
        ->assertOk()
        ->assertSee(SecurityRegion::Europe->getLabel())
        ->assertDontSee(SecurityRegion::AsiaPacific->getLabel());
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

it('renders active budgets and hides inactive ones', function (): void {
    $active = Budget::factory()->create([
        'category_id' => Category::factory()->create(['group' => CategoryGroup::VarExpenses])->id,
        'is_active' => true,
    ]);

    $inactive = Budget::factory()->create([
        'category_id' => Category::factory()->create(['group' => CategoryGroup::VarExpenses])->id,
        'is_active' => false,
    ]);

    livewire(BudgetOverviewWidget::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$inactive]);
});

it('hides budgets that have not started or have already ended', function (): void {
    $running = Budget::factory()->create([
        'category_id' => Category::factory()->create(['group' => CategoryGroup::VarExpenses])->id,
        'starts_at' => today()->subMonth(),
        'ends_at' => today(),
    ]);

    $upcoming = Budget::factory()->create([
        'category_id' => Category::factory()->create(['group' => CategoryGroup::VarExpenses])->id,
        'starts_at' => today()->addDay(),
    ]);

    $ended = Budget::factory()->create([
        'category_id' => Category::factory()->create(['group' => CategoryGroup::VarExpenses])->id,
        'starts_at' => today()->subMonths(2),
        'ends_at' => today()->subDay(),
    ]);

    livewire(BudgetOverviewWidget::class)
        ->assertCanSeeTableRecords([$running])
        ->assertCanNotSeeTableRecords([$upcoming, $ended]);
});

it('decides which budgets are running by the date in the timezone of the user', function (): void {
    // UTC+14, so at noon UTC the user's local date is already the next day
    auth()->user()->update(['timezone' => 'Pacific/Kiritimati']);
    $this->travelTo(today('UTC')->setTime(12, 0));

    $startingToday = Budget::factory()->create([
        'category_id' => Category::factory()->create(['group' => CategoryGroup::VarExpenses])->id,
        'starts_at' => today('UTC')->addDay(),
    ]);

    livewire(BudgetOverviewWidget::class)
        ->assertCanSeeTableRecords([$startingToday]);
});

it('is visible only when active budgets exist', function (): void {
    expect(BudgetOverviewWidget::canView())->toBeFalse();

    Budget::factory()->create([
        'category_id' => Category::factory()->create(['group' => CategoryGroup::VarExpenses])->id,
        'is_active' => true,
    ]);

    expect(BudgetOverviewWidget::canView())->toBeTrue();
});
