<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\CategoryGroup;
use App\Models\Category;
use App\Models\CategoryStatistic;
use App\Models\User;

use function Pest\Laravel\getJson;

beforeEach(fn () => asUser());

describe('Statistic API', function () {
    test('index returns a list of statistics', function () {
        $user = User::firstOrFail();
        $category = Category::factory()->create(['user_id' => $user->id]);
        CategoryStatistic::factory()->count(3)->create(['category_id' => $category->id, 'year' => 2025]);

        actingAsWithAbilities($user, ApiAbility::STATISTIC->all());

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
        $user = User::firstOrFail();
        $category = Category::factory()->create(['user_id' => $user->id]);
        CategoryStatistic::factory()->create(['category_id' => $category->id, 'year' => 2024]);
        CategoryStatistic::factory()->create(['category_id' => $category->id, 'year' => 2025]);

        actingAsWithAbilities($user, ApiAbility::STATISTIC->all());

        getJson(route('api.statistics.index', ['filter[year]' => 2025]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.year', 2025)
            ->assertJsonMissing(['year' => 2024]);
    });

    test('index can filter statistics by category_id', function () {
        $user = User::firstOrFail();
        $category1 = Category::factory()->create(['user_id' => $user->id]);
        $category2 = Category::factory()->create(['user_id' => $user->id]);
        CategoryStatistic::factory()->create(['category_id' => $category1->id, 'year' => 2025]);
        CategoryStatistic::factory()->create(['category_id' => $category2->id, 'year' => 2025]);

        actingAsWithAbilities($user, ApiAbility::STATISTIC->all());

        getJson(route('api.statistics.index', ['filter[category_id]' => $category1->id]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category_id', $category1->id)
            ->assertJsonMissing(['category_id' => $category2->id]);
    });

    test('index can filter statistics by category type and group', function () {
        $user = User::firstOrFail();
        $category1 = Category::factory()->create([
            'user_id' => $user->id,
            'group' => CategoryGroup::VarExpenses,
        ]);
        $category2 = Category::factory()->create([
            'user_id' => $user->id,
            'group' => CategoryGroup::FixRevenues,
        ]);

        CategoryStatistic::factory()->create(['category_id' => $category1->id, 'year' => 2025]);
        CategoryStatistic::factory()->create(['category_id' => $category2->id, 'year' => 2025]);

        actingAsWithAbilities($user, ApiAbility::STATISTIC->all());

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
        $user = User::firstOrFail();
        $category = Category::factory()->create(['user_id' => $user->id]);
        CategoryStatistic::factory()->create(['category_id' => $category->id, 'year' => 2025]);

        actingAsWithAbilities($user, ApiAbility::STATISTIC->all());

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
        $user = User::firstOrFail();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $statistic = CategoryStatistic::factory()->create([
            'category_id' => $category->id,
            'year' => 2025,
            'jan' => 100.50,
            'feb' => 200.75,
        ]);

        actingAsWithAbilities($user, ApiAbility::STATISTIC->all());

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
        $user = User::firstOrFail();
        actingAsWithAbilities($user, []);

        getJson(route('api.statistics.index'))
            ->assertStatus(403);
    });

    test('index only returns statistics for user categories', function () {
        $user = User::firstOrFail();
        $anotherUser = User::factory()->create();

        $userCategory = Category::factory()->create(['user_id' => $user->id]);
        $anotherUserCategory = Category::factory()->create(['user_id' => $anotherUser->id]);

        CategoryStatistic::factory()->create(['category_id' => $userCategory->id, 'year' => 2025]);
        CategoryStatistic::factory()->create(['category_id' => $anotherUserCategory->id, 'year' => 2025]);

        actingAsWithAbilities($user, ApiAbility::STATISTIC->all());

        getJson(route('api.statistics.index'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category_id', $userCategory->id);
    });
});
