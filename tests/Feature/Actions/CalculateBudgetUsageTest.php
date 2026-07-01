<?php

declare(strict_types=1);

use App\Actions\CalculateBudgetUsage;
use App\Data\BudgetUsage;
use App\Enums\CategoryGroup;
use App\Enums\PeriodUnit;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\CarbonImmutable;

beforeEach(fn () => asUser());

function calculateBudgetUsage(Budget $budget): BudgetUsage
{
    return resolve(CalculateBudgetUsage::class)($budget);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function expenseBudget(array $attributes = []): Budget
{
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    return Budget::factory()->create(array_merge([
        'category_id' => $category->id,
        'amount' => 500.0,
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth(),
    ], $attributes));
}

function recordSpend(Budget $budget, float $amount, CarbonImmutable $date, TransactionType $type = TransactionType::Expense): void
{
    Transaction::factory()->create([
        'account_id' => Account::factory()->create()->id,
        'category_id' => $budget->category_id,
        'type' => $type,
        'amount' => $amount,
        'date_time' => $date,
    ]);
}

it('calculates spend, remaining and percentage for the current period', function (): void {
    $budget = expenseBudget(['amount' => 500.0]);

    recordSpend($budget, 120.0, now()->toImmutable());
    recordSpend($budget, 80.0, now()->toImmutable());

    $usage = calculateBudgetUsage($budget);

    expect($usage->budgeted)->toBe(500.0)
        ->and($usage->spent)->toBe(200.0)
        ->and($usage->remaining)->toBe(300.0)
        ->and($usage->percentage)->toBe(40.0)
        ->and($usage->hasReachedThreshold(50))->toBeFalse()
        ->and($usage->hasReachedThreshold(40))->toBeTrue();
});

it('only counts expense transactions inside the current window', function (): void {
    $budget = expenseBudget(['amount' => 500.0, 'starts_at' => today()->startOfMonth()]);

    recordSpend($budget, 100.0, now()->toImmutable());                                  // counted
    recordSpend($budget, 50.0, now()->toImmutable(), TransactionType::Revenue);         // wrong type
    recordSpend($budget, 50.0, now()->toImmutable(), TransactionType::Transfer);        // wrong type
    recordSpend($budget, 999.0, today()->startOfMonth()->subDay()->toImmutable());      // previous period

    $usage = calculateBudgetUsage($budget);

    expect($usage->spent)->toBe(100.0);
});

it('exposes the current monthly window boundaries', function (): void {
    $budget = expenseBudget(['starts_at' => today()->startOfMonth()]);

    $usage = calculateBudgetUsage($budget);

    expect($usage->periodStart->toDateString())->toBe(today()->startOfMonth()->toDateString())
        ->and($usage->periodEnd->toDateString())->toBe(today()->startOfMonth()->addMonth()->toDateString());
});

it('advances to the window that contains today for older budgets', function (): void {
    $budget = expenseBudget([
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth()->subMonths(5),
    ]);

    recordSpend($budget, 75.0, now()->toImmutable());

    $usage = calculateBudgetUsage($budget);

    expect($usage->periodStart->toDateString())->toBe(today()->startOfMonth()->toDateString())
        ->and($usage->spent)->toBe(75.0);
});

it('returns an empty window before the budget has started', function (): void {
    $start = today()->startOfMonth()->addMonth();
    $budget = expenseBudget(['starts_at' => $start]);

    $usage = calculateBudgetUsage($budget);

    expect($usage->spent)->toBe(0.0)
        ->and($usage->percentage)->toBe(0.0)
        ->and($usage->periodStart->toDateString())->toBe($start->toDateString());
});

it('anchors to the final period for an ended budget', function (): void {
    $budget = expenseBudget([
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth()->subMonths(3),
        'ends_at' => today()->startOfMonth()->subMonth()->subDay(),
    ]);

    // Spend within the final period (the month before last).
    recordSpend($budget, 60.0, today()->startOfMonth()->subMonths(2)->addDays(2)->toImmutable());

    $usage = calculateBudgetUsage($budget);

    expect($usage->periodStart->toDateString())->toBe(today()->startOfMonth()->subMonths(2)->toDateString())
        ->and($usage->spent)->toBe(60.0);
});

it('reports zero percentage when the budget amount is zero', function (): void {
    $budget = expenseBudget(['amount' => 0.0]);

    recordSpend($budget, 10.0, now()->toImmutable());

    $usage = calculateBudgetUsage($budget);

    expect($usage->percentage)->toBe(0.0)
        ->and($usage->remaining)->toBe(-10.0);
});

it('handles weekly periods', function (): void {
    $budget = expenseBudget([
        'period_unit' => PeriodUnit::Week,
        'period_frequency' => 1,
        'amount' => 200.0,
        'starts_at' => today()->startOfWeek(),
    ]);

    recordSpend($budget, 50.0, now()->toImmutable());
    recordSpend($budget, 999.0, today()->startOfWeek()->subDay()->toImmutable()); // previous week

    $usage = calculateBudgetUsage($budget);

    expect($usage->spent)->toBe(50.0)
        ->and($usage->periodStart->toDateString())->toBe(today()->startOfWeek()->toDateString())
        ->and($usage->periodEnd->toDateString())->toBe(today()->startOfWeek()->addWeek()->toDateString());
});

it('keeps the start day of month for monthly periods after shorter months', function (): void {
    $year = today()->startOfYear();
    $this->travelTo($year->month(4)->day(15)->setTime(12, 0));

    $budget = expenseBudget(['starts_at' => $year->endOfMonth()->startOfDay()]);

    $usage = calculateBudgetUsage($budget);

    expect($usage->periodStart->toDateString())->toBe($year->month(3)->endOfMonth()->toDateString())
        ->and($usage->periodEnd->toDateString())->toBe($year->month(4)->endOfMonth()->toDateString());
});

it('cuts the final period off at the end date', function (): void {
    $startsAt = today()->startOfMonth()->subMonths(2);
    $endsAt = $startsAt->addDays(9);

    $budget = expenseBudget(['starts_at' => $startsAt, 'ends_at' => $endsAt]);

    recordSpend($budget, 40.0, $endsAt->setTime(18, 0));
    recordSpend($budget, 999.0, $endsAt->addDay()->setTime(9, 0));

    $usage = calculateBudgetUsage($budget);

    expect($usage->periodStart->toDateString())->toBe($startsAt->toDateString())
        ->and($usage->periodEnd->toDateString())->toBe($endsAt->addDay()->toDateString())
        ->and($usage->spent)->toBe(40.0);
});

it('starts the period on the shortened month end when the start day does not exist', function (): void {
    $year = today()->startOfYear();
    $this->travelTo($year->month(2)->endOfMonth()->setTime(12, 0));

    $budget = expenseBudget(['starts_at' => $year->endOfMonth()->startOfDay()]);

    $usage = calculateBudgetUsage($budget);

    expect($usage->periodStart->toDateString())->toBe($year->month(2)->endOfMonth()->toDateString())
        ->and($usage->periodEnd->toDateString())->toBe($year->month(3)->endOfMonth()->toDateString());
});
