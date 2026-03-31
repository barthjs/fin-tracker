<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\CategoryGroup;
use App\Models\Category;
use App\Models\CategoryStatistic;
use App\Models\User;

use function Pest\Laravel\getJson;

beforeEach(function () {
    $user = User::factory()->verified()->create();
    $this->user = $user;
});

describe('Statistic API', function () {
    test('index returns a list of statistics', function () {
        actingAsWithAbilities($this->user, ApiAbility::STATISTIC->all());

        $category = Category::factory()->create(['user_id' => $this->user->id]);
        CategoryStatistic::factory()->count(3)->create(['category_id' => $category->id, 'year' => 2025]);

        getJson(route('api.statistics.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'category_id',
                        'year',
                        'jan',
                        'feb',
                        'mar',
                        'apr',
                        'may',
                        'jun',
                        'jul',
                        'aug',
                        'sep',
                        'oct',
                        'nov',
                        'dec',
                        'yearly_sum',
                    ],
                ],
                'links',
                'meta',
            ]);
    });

    test('index can filter statistics by year', function () {
        actingAsWithAbilities($this->user, ApiAbility::STATISTIC->all());

        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $statistic2024 = CategoryStatistic::factory()->create(['category_id' => $category->id, 'year' => 2024]);
        $statistic2025 = CategoryStatistic::factory()->create(['category_id' => $category->id, 'year' => 2025]);

        getJson(route('api.statistics.index', ['filter[year]' => $statistic2025->year]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.year', $statistic2025->year)
            ->assertJsonMissing(['year' => $statistic2024->year]);
    });

    test('index can filter statistics by category_id', function () {
        actingAsWithAbilities($this->user, ApiAbility::STATISTIC->all());

        $category1 = Category::factory()->create(['user_id' => $this->user->id]);
        $category2 = Category::factory()->create(['user_id' => $this->user->id]);
        CategoryStatistic::factory()->create(['category_id' => $category1->id, 'year' => 2025]);
        CategoryStatistic::factory()->create(['category_id' => $category2->id, 'year' => 2025]);

        getJson(route('api.statistics.index', ['filter[category_id]' => $category1->id]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category_id', $category1->id)
            ->assertJsonMissing(['category_id' => $category2->id]);
    });

    test('index can filter statistics by category type and group', function () {
        actingAsWithAbilities($this->user, ApiAbility::STATISTIC->all());

        $category1 = Category::factory()->create([
            'user_id' => $this->user->id,
            'group' => CategoryGroup::VarExpenses,
        ]);
        $category2 = Category::factory()->create([
            'user_id' => $this->user->id,
            'group' => CategoryGroup::FixRevenues,
        ]);

        CategoryStatistic::factory()->create(['category_id' => $category1->id, 'year' => 2025]);
        CategoryStatistic::factory()->create(['category_id' => $category2->id, 'year' => 2025]);

        getJson(route('api.statistics.index', ['filter[category.group]' => CategoryGroup::VarExpenses->value]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category_id', $category1->id);

        getJson(route('api.statistics.index', ['filter[category.type]' => $category2->type->value]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category_id', $category2->id);
    });

    test('index can include category relationship', function () {
        actingAsWithAbilities($this->user, ApiAbility::STATISTIC->all());

        $category = Category::factory()->create(['user_id' => $this->user->id]);
        CategoryStatistic::factory()->create(['category_id' => $category->id, 'year' => 2025]);

        getJson(route('api.statistics.index', ['include' => 'category']))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'category' => [
                            'id',
                            'name',
                        ],
                    ],
                ],
            ]);
    });

    test('show returns a single statistic with category', function () {
        actingAsWithAbilities($this->user, ApiAbility::STATISTIC->all());

        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $statistic = CategoryStatistic::factory()->create([
            'category_id' => $category->id,
            'year' => 2025,
            'jan' => 100.50,
            'feb' => 200.75,
        ]);

        getJson(route('api.statistics.show', $statistic))
            ->assertOk()
            ->assertJsonPath('data.id', $statistic->id)
            ->assertJsonPath('data.category_id', $category->id)
            ->assertJsonPath('data.year', 2025)
            ->assertJsonPath('data.jan', 100.50)
            ->assertJsonPath('data.feb', 200.75)
            ->assertJsonPath('data.yearly_sum', $statistic->yearlySum())
            ->assertJsonStructure([
                'data' => [
                    'category' => [
                        'id',
                        'name',
                    ],
                ],
            ]);
    });

    test('forbidden access without correct ability', function () {
        actingAsWithAbilities($this->user, []);

        getJson(route('api.statistics.index'))
            ->assertStatus(403);
    });

    test('index only returns statistics for user categories', function () {
        actingAsWithAbilities($this->user, ApiAbility::STATISTIC->all());

        $anotherUser = User::factory()->create();

        $userCategory = Category::factory()->create(['user_id' => $this->user->id]);
        $anotherUserCategory = Category::factory()->create(['user_id' => $anotherUser->id]);

        CategoryStatistic::factory()->create(['category_id' => $userCategory->id, 'year' => 2025]);
        CategoryStatistic::factory()->create(['category_id' => $anotherUserCategory->id, 'year' => 2025]);

        getJson(route('api.statistics.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category_id', $userCategory->id);
    });
});
