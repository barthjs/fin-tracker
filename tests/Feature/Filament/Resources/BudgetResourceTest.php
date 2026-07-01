<?php

declare(strict_types=1);

use App\Data\BudgetUsage;
use App\Enums\CategoryGroup;
use App\Enums\NotificationEventType;
use App\Enums\PeriodUnit;
use App\Enums\TransactionType;
use App\Filament\Resources\Budgets\BudgetResource;
use App\Filament\Resources\Budgets\Pages\ListBudgets;
use App\Filament\Resources\Budgets\Pages\ViewBudget;
use App\Filament\Resources\Budgets\RelationManagers\TransactionsRelationManager;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\NotificationTarget;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

function expenseCategory(): Category
{
    return Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function makeBudget(array $attributes = []): Budget
{
    return Budget::factory()->create(array_merge([
        'category_id' => expenseCategory()->id,
    ], $attributes));
}

it('renders the list page', function (): void {
    $budgets = collect(range(1, 3))->map(fn (): Budget => makeBudget());

    livewire(ListBudgets::class)
        ->assertOk()
        ->assertCanSeeTableRecords($budgets)
        ->assertActionExists('import')
        ->assertActionExists('export');
});

it('scopes budgets to the user and filters by category, period and inactivity', function (): void {
    $categoryX = expenseCategory();
    $categoryY = expenseCategory();

    $monthly = makeBudget(['category_id' => $categoryX->id, 'period_unit' => PeriodUnit::Month, 'is_active' => true]);
    $yearly = makeBudget(['category_id' => $categoryY->id, 'period_unit' => PeriodUnit::Year, 'is_active' => true]);
    $inactive = makeBudget(['is_active' => false]);

    $other = User::factory()->create();
    $otherCategory = Category::factory()->create(['user_id' => $other->id, 'group' => CategoryGroup::VarExpenses]);
    $otherBudget = Budget::factory()->create(['category_id' => $otherCategory->id]);

    livewire(ListBudgets::class)
        ->assertCanSeeTableRecords([$monthly, $yearly, $inactive])
        ->assertCanNotSeeTableRecords([$otherBudget])
        ->filterTable('category_id', $categoryY)
        ->assertCanSeeTableRecords([$yearly])
        ->assertCanNotSeeTableRecords([$monthly])
        ->resetTableFilters()
        ->filterTable('period_unit', PeriodUnit::Month)
        ->assertCanSeeTableRecords([$monthly])
        ->assertCanNotSeeTableRecords([$yearly])
        ->resetTableFilters()
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactive])
        ->assertCanNotSeeTableRecords([$monthly, $yearly]);
});

it('can create a budget', function (): void {
    $category = expenseCategory();

    $data = [
        'category_id' => $category->id,
        'amount' => 400.0,
        'period_unit' => PeriodUnit::Month->value,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth()->toDateString(),
        'is_active' => true,
        'notify_on_exceed' => false,
    ];

    livewire(ListBudgets::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('budgets', [
        'category_id' => $category->id,
        'amount' => 400.0,
    ]);
});

it('can create a budget with notification targets', function (): void {
    $category = expenseCategory();
    $target = NotificationTarget::factory()->create();

    $data = [
        'category_id' => $category->id,
        'amount' => 250.0,
        'period_unit' => PeriodUnit::Month->value,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth()->toDateString(),
        'is_active' => true,
        'warning_threshold' => 80,
        'notify_on_exceed' => true,
        'exceed_targets' => [$target->id],
    ];

    livewire(ListBudgets::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    $budget = Budget::query()->where('category_id', $category->id)->firstOrFail();

    expect($budget->warning_threshold)->toBe(80)
        ->and($budget->getNotificationTargetsForEvent(NotificationEventType::BUDGET_ALERT)->pluck('id'))
        ->toContain($target->id);
});

it('enforces a single budget per category', function (): void {
    $category = expenseCategory();
    makeBudget(['category_id' => $category->id]);

    $data = [
        'category_id' => $category->id,
        'amount' => 100.0,
        'period_unit' => PeriodUnit::Month->value,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth()->toDateString(),
        'is_active' => true,
        'notify_on_exceed' => false,
    ];

    livewire(ListBudgets::class)
        ->callAction('create', $data)
        ->assertHasFormErrors(['category_id']);
});

it('validates the warning threshold range', function (): void {
    $category = expenseCategory();

    $data = [
        'category_id' => $category->id,
        'amount' => 100.0,
        'period_unit' => PeriodUnit::Month->value,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth()->toDateString(),
        'is_active' => true,
        'warning_threshold' => 100,
        'notify_on_exceed' => false,
    ];

    livewire(ListBudgets::class)
        ->callAction('create', $data)
        ->assertHasFormErrors(['warning_threshold']);
});

it('rejects an amount of zero', function (): void {
    $data = [
        'category_id' => expenseCategory()->id,
        'amount' => 0,
        'period_unit' => PeriodUnit::Month->value,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth()->toDateString(),
        'is_active' => true,
        'notify_on_exceed' => false,
    ];

    livewire(ListBudgets::class)
        ->callAction('create', $data)
        ->assertHasFormErrors(['amount']);
});

it('can edit a budget', function (): void {
    $budget = makeBudget(['amount' => 100.0]);

    $data = [
        'category_id' => $budget->category_id,
        'amount' => 250.0,
        'period_unit' => $budget->period_unit->value,
        'period_frequency' => $budget->period_frequency,
        'starts_at' => $budget->starts_at->toDateString(),
        'is_active' => true,
        'notify_on_exceed' => false,
    ];

    livewire(ViewBudget::class, ['record' => $budget->id])
        ->callAction('edit', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('budgets', ['id' => $budget->id, 'amount' => 250.0]);
});

it('can delete a budget', function (): void {
    $budget = makeBudget();

    livewire(ViewBudget::class, ['record' => $budget->id])
        ->callAction('delete')
        ->assertHasNoActionErrors();

    assertDatabaseMissing('budgets', ['id' => $budget->id]);
});

it('can bulk delete budgets', function (): void {
    $budgets = collect(range(1, 3))->map(fn (): Budget => makeBudget());

    livewire(ListBudgets::class)
        ->callTableBulkAction('delete', $budgets);

    foreach ($budgets as $budget) {
        $this->assertModelMissing($budget);
    }
});

it('shows the current period and its spending on the view page', function (): void {
    $budget = makeBudget(['amount' => 200.0, 'starts_at' => today()->startOfMonth()]);

    Transaction::factory()->create([
        'category_id' => $budget->category_id,
        'type' => TransactionType::Expense,
        'amount' => 50.0,
        'date_time' => now(),
    ]);

    livewire(ViewBudget::class, ['record' => $budget->id])
        ->assertOk()
        ->assertSee(today()->startOfMonth()->format('d.m.Y').' – '.today()->endOfMonth()->format('d.m.Y'))
        ->assertSee('25%');
});

it('lists only the expenses of the current period in the transactions relation manager', function (): void {
    $budget = makeBudget(['starts_at' => today()->startOfMonth()]);

    $current = Transaction::factory()->create([
        'category_id' => $budget->category_id,
        'type' => TransactionType::Expense,
        'date_time' => now(),
    ]);

    $previousPeriod = Transaction::factory()->create([
        'category_id' => $budget->category_id,
        'type' => TransactionType::Expense,
        'date_time' => today()->startOfMonth()->subDay()->setTime(12, 0),
    ]);

    $otherCategory = Transaction::factory()->create([
        'type' => TransactionType::Expense,
        'date_time' => now(),
    ]);

    livewire(TransactionsRelationManager::class, [
        'ownerRecord' => $budget,
        'pageClass' => ViewBudget::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords([$current])
        ->assertCanNotSeeTableRecords([$previousPeriod, $otherCategory]);
});

it('colors the progress bar by the warning threshold and the budget limit', function (?int $warningThreshold, float $percentage, string $color): void {
    $budget = makeBudget(['warning_threshold' => $warningThreshold]);

    $usage = new BudgetUsage(
        periodStart: today()->startOfMonth(),
        periodEnd: today()->startOfMonth()->addMonth(),
        budgeted: 100.0,
        spent: $percentage,
        remaining: 100.0 - $percentage,
        percentage: $percentage,
    );

    expect(BudgetResource::progressState($budget, $usage)['color'])->toBe($color);
})->with([
    'below the warning threshold' => [80, 50.0, 'primary'],
    'at the warning threshold' => [80, 80.0, 'warning'],
    'without a warning threshold' => [null, 95.0, 'primary'],
    'at the budget limit' => [80, 100.0, 'danger'],
]);

it('uses the category name as record title', function (): void {
    $budget = makeBudget();

    expect(BudgetResource::getRecordTitle($budget))->toBe($budget->category->name)
        ->and(BudgetResource::getRecordTitle(null))->toBe(__('budget.label'));
});

it('creates a transaction in the budget category from the transactions relation manager', function (): void {
    $budget = makeBudget();
    $account = Account::factory()->create();

    livewire(TransactionsRelationManager::class, [
        'ownerRecord' => $budget,
        'pageClass' => ViewBudget::class,
    ])
        ->callAction(TestAction::make('create')->table(), [
            'date_time' => now(),
            'account_id' => $account->id,
            'amount' => 25.5,
        ])
        ->assertHasNoFormErrors();

    assertDatabaseHas('transactions', [
        'account_id' => $account->id,
        'category_id' => $budget->category_id,
        'amount' => 25.5,
    ]);
});

it('preselects the default notification targets when notifications are switched on while editing', function (): void {
    $budget = makeBudget(['notify_on_exceed' => false]);
    $defaultTarget = NotificationTarget::factory()->create(['is_default' => true]);
    NotificationTarget::factory()->create(['is_default' => false]);

    livewire(ViewBudget::class, ['record' => $budget->id])
        ->mountAction('edit')
        ->assertSchemaStateSet(['exceed_targets' => []])
        ->fillForm(['notify_on_exceed' => true])
        ->assertSchemaStateSet(['exceed_targets' => [$defaultTarget->id]]);
});

it('keeps the selected notification targets when notifications are switched on again', function (): void {
    NotificationTarget::factory()->create(['is_default' => true]);
    $selectedTarget = NotificationTarget::factory()->create(['is_default' => false]);

    livewire(ListBudgets::class)
        ->mountAction('create')
        ->fillForm(['exceed_targets' => [$selectedTarget->id]])
        ->fillForm(['notify_on_exceed' => true])
        ->assertSchemaStateSet(['exceed_targets' => [$selectedTarget->id]]);
});
