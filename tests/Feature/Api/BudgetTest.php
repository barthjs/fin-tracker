<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\CategoryGroup;
use App\Enums\PeriodUnit;
use App\Models\Budget;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function (): void {
    $user = User::factory()->verified()->create();
    $this->user = $user;
});

describe('Budget API', function (): void {
    test('index returns a list of budgets', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        foreach (range(1, 3) as $ignored) {
            $category = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);
            Budget::factory()->create(['category_id' => $category->id]);
        }

        $anotherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $anotherUser->id, 'group' => CategoryGroup::VarExpenses]);
        Budget::factory()->create(['category_id' => $category->id]);

        getJson(route('api.budgets.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'created_at',
                        'updated_at',
                        'category_id',
                        'amount',
                        'period_unit',
                        'period_frequency',
                        'starts_at',
                        'ends_at',
                        'warning_threshold',
                        'notify_on_exceed',
                        'last_warned_at',
                        'last_exceeded_at',
                        'is_active',
                    ],
                ],
                'links',
                'meta',
            ]);
    });

    test('index can filter budgets by category', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $categoryA = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);
        $categoryB = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);
        $matchingBudget = Budget::factory()->create(['category_id' => $categoryA->id]);
        $nonMatchingBudget = Budget::factory()->create(['category_id' => $categoryB->id]);

        getJson(route('api.budgets.index', ['filter[category_id]' => $categoryA->id]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matchingBudget->id)
            ->assertJsonMissing(['id' => $nonMatchingBudget->id]);
    });

    test('store creates a new budget', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $category = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);

        $data = [
            'category_id' => $category->id,
            'amount' => 400.5,
            'period_unit' => PeriodUnit::Month->value,
            'period_frequency' => 1,
            'starts_at' => today()->startOfMonth()->toDateString(),
            'warning_threshold' => 80,
            'notify_on_exceed' => true,
            'is_active' => true,
        ];

        postJson(route('api.budgets.store'), $data)
            ->assertCreated()
            ->assertJsonPath('data.category_id', $data['category_id'])
            ->assertJsonPath('data.amount', $data['amount'])
            ->assertJsonPath('data.period_unit', $data['period_unit'])
            ->assertJsonPath('data.warning_threshold', $data['warning_threshold'])
            ->assertJsonPath('data.notify_on_exceed', $data['notify_on_exceed']);

        assertDatabaseHas('budgets', [
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
        ]);
    });

    test('store fails with invalid data', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        postJson(route('api.budgets.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount', 'category_id', 'period_unit', 'period_frequency', 'starts_at']);
    });

    test('store rejects a category of another user', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $foreignCategory = Category::factory()->create([
            'user_id' => User::factory()->create()->id,
            'group' => CategoryGroup::VarExpenses,
        ]);

        postJson(route('api.budgets.store'), Budget::factory()->make(['category_id' => $foreignCategory->id])->toArray())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    });

    test('store rejects a revenue category', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $revenueCategory = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarRevenues]);

        postJson(route('api.budgets.store'), Budget::factory()->make(['category_id' => $revenueCategory->id])->toArray())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    });

    test('store rejects a second budget for the same category', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $category = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);
        Budget::factory()->create(['category_id' => $category->id]);

        postJson(route('api.budgets.store'), Budget::factory()->make(['category_id' => $category->id])->toArray())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id']);
    });

    test('store rejects an amount of zero', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $category = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);

        postJson(route('api.budgets.store'), Budget::factory()->make(['category_id' => $category->id, 'amount' => 0])->toArray())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);
    });

    test('update rejects an end date before the stored start date', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $category = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);
        $budget = Budget::factory()->create(['category_id' => $category->id]);

        patchJson(route('api.budgets.update', $budget), ['ends_at' => $budget->starts_at->subDay()->toDateString()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ends_at']);
    });

    test('update rejects a start date after the stored end date', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $category = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);
        $budget = Budget::factory()->create([
            'category_id' => $category->id,
            'ends_at' => today()->endOfMonth(),
        ]);

        patchJson(route('api.budgets.update', $budget), ['starts_at' => today()->endOfMonth()->addDay()->toDateString()])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['starts_at']);
    });

    test('show returns a single budget', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $category = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);
        $budget = Budget::factory()->create(['category_id' => $category->id]);

        getJson(route('api.budgets.show', $budget))
            ->assertOk()
            ->assertJsonPath('data.id', $budget->id)
            ->assertJsonPath('data.category_id', $budget->category_id)
            ->assertJsonPath('data.amount', $budget->amount)
            ->assertJsonPath('data.period_unit', $budget->period_unit->value)
            ->assertJsonPath('data.is_active', $budget->is_active);
    });

    test('show returns not found for a budget of another user', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $category = Category::factory()->create(['user_id' => User::factory()->create()->id, 'group' => CategoryGroup::VarExpenses]);
        $budget = Budget::factory()->create(['category_id' => $category->id]);

        getJson(route('api.budgets.show', $budget))
            ->assertNotFound();
    });

    test('update modifies an existing budget', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $category = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);
        $budget = Budget::factory()->create(['category_id' => $category->id, 'amount' => 100.0]);

        $data = [
            'category_id' => $category->id,
            'amount' => 250.75,
            'period_unit' => PeriodUnit::Year->value,
            'period_frequency' => 1,
            'starts_at' => $budget->starts_at->toDateString(),
            'is_active' => false,
        ];

        putJson(route('api.budgets.update', $budget), $data)
            ->assertOk()
            ->assertJsonPath('data.amount', $data['amount'])
            ->assertJsonPath('data.period_unit', $data['period_unit'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'amount' => $data['amount'],
        ]);
    });

    test('destroy deletes a budget', function (): void {
        actingAsWithAbilities($this->user, ApiAbility::BUDGET->all());

        $category = Category::factory()->create(['user_id' => $this->user->id, 'group' => CategoryGroup::VarExpenses]);
        $budget = Budget::factory()->create(['category_id' => $category->id]);

        deleteJson(route('api.budgets.destroy', $budget))
            ->assertNoContent();

        assertDatabaseMissing('budgets', ['id' => $budget->id]);
    });

    test('forbidden access without correct ability', function (): void {
        actingAsWithAbilities($this->user, []);

        getJson(route('api.budgets.index'))
            ->assertStatus(403);
    });
});
