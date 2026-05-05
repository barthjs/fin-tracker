<?php

declare(strict_types=1);

use App\Actions\GetCategoryChartData;
use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\CategoryStatistic;

beforeEach(fn () => asUser());

it('returns labels, series and colors sorted descending by current month sum', function (): void {
    $monthColumn = mb_strtolower(now()->format('M'));
    $year = now()->year;

    $low = Category::factory()->create([
        'group' => CategoryGroup::VarExpenses,
        'name' => 'Low',
        'color' => '#111111',
    ]);
    $high = Category::factory()->create([
        'group' => CategoryGroup::VarExpenses,
        'name' => 'High',
        'color' => '#222222',
    ]);

    CategoryStatistic::factory()->create(['category_id' => $low->id, 'year' => $year, $monthColumn => 50.0]);
    CategoryStatistic::factory()->create(['category_id' => $high->id, 'year' => $year, $monthColumn => 100.0]);

    $data = resolve(GetCategoryChartData::class)(TransactionType::Expense);

    expect($data['labels'])->toBe(['High', 'Low'])
        ->and($data['series'])->toBe([100.0, 50.0])
        ->and($data['colors'])->toBe(['#222222', '#111111']);
});

it('excludes inactive categories and categories of other types', function (): void {
    $monthColumn = mb_strtolower(now()->format('M'));
    $year = now()->year;

    $active = Category::factory()->create([
        'group' => CategoryGroup::VarExpenses,
        'name' => 'Active',
    ]);
    $inactive = Category::factory()->create([
        'group' => CategoryGroup::VarExpenses,
        'name' => 'Inactive',
        'is_active' => false,
    ]);
    $revenue = Category::factory()->create([
        'group' => CategoryGroup::VarRevenues,
        'name' => 'Revenue',
    ]);

    CategoryStatistic::factory()->create(['category_id' => $active->id, 'year' => $year, $monthColumn => 10.0]);
    CategoryStatistic::factory()->create(['category_id' => $inactive->id, 'year' => $year, $monthColumn => 999.0]);
    CategoryStatistic::factory()->create(['category_id' => $revenue->id, 'year' => $year, $monthColumn => 999.0]);

    $data = resolve(GetCategoryChartData::class)(TransactionType::Expense);

    expect($data['labels'])->toBe(['Active'])
        ->and($data['series'])->toBe([10.0]);
});

it('returns empty arrays when there are no categories', function (): void {
    $data = resolve(GetCategoryChartData::class)(TransactionType::Expense);

    expect($data['labels'])->toBe([])
        ->and($data['series'])->toBe([])
        ->and($data['colors'])->toBe([]);
});
