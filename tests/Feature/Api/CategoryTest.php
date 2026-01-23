<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\CategoryGroup;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(fn () => asUser());

describe('Category API', function () {
    test('index returns a list of categories', function () {
        $user = User::firstOrFail();
        Category::factory()->count(3)->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        Category::factory()->create(['user_id' => $anotherUser->id]);

        actingAsWithAbilities($user, ApiAbility::CATEGORY->all());

        getJson(route('api.categories.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'group',
                        'type',
                        'color',
                        'is_active',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    });

    test('index can filter categories by name', function () {
        $user = User::firstOrFail();
        Category::factory()->create(['name' => 'Food', 'user_id' => $user->id]);
        Category::factory()->create(['name' => 'Rent', 'user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::CATEGORY->all());

        getJson(route('api.categories.index', ['filter[name]' => 'Food']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Food')
            ->assertJsonMissing(['name' => 'Rent']);
    });

    test('store creates a new category', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, ApiAbility::CATEGORY->all());

        $data = [
            'name' => 'Travel',
            'group' => CategoryGroup::VarExpenses->value,
            'color' => '#ff0000',
            'is_active' => true,
        ];

        postJson(route('api.categories.store'), $data)
            ->assertCreated()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.group', $data['group'])
            ->assertJsonPath('data.color', $data['color'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('categories', array_merge($data, ['user_id' => $user->id]));
    });

    test('show returns a single category', function () {
        $user = User::firstOrFail();
        $category = Category::factory()->create(['user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::CATEGORY->all());

        getJson(route('api.categories.show', $category))
            ->assertOk()
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', $category->name)
            ->assertJsonPath('data.group', $category->group->value)
            ->assertJsonPath('data.type', $category->type->value)
            ->assertJsonPath('data.color', $category->color)
            ->assertJsonPath('data.is_active', $category->is_active);
    });

    test('update modifies an existing category', function () {
        $user = User::firstOrFail();
        $category = Category::factory()->create(['name' => 'Old Name', 'user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::CATEGORY->all());

        $data = [
            'name' => 'Updated Name',
            'group' => CategoryGroup::FixRevenues->value,
            'color' => '#00ff00',
            'is_active' => false,
        ];

        putJson(route('api.categories.update', $category), $data)
            ->assertOk()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.group', $data['group'])
            ->assertJsonPath('data.color', $data['color'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('categories', array_merge($data, ['id' => $category->id]));
    });

    test('destroy deletes a category', function () {
        $user = User::firstOrFail();
        $category = Category::factory()->create(['user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::CATEGORY->all());

        deleteJson(route('api.categories.destroy', $category))
            ->assertNoContent();

        assertDatabaseMissing('categories', ['id' => $category->id]);
    });

    test('destroy fails if category has transactions', function () {
        $user = User::firstOrFail();
        $category = Category::factory()->create(['user_id' => $user->id]);
        Transaction::factory()->create(['category_id' => $category->id]);

        actingAsWithAbilities($user, ApiAbility::CATEGORY->all());

        deleteJson(route('api.categories.destroy', $category))
            ->assertForbidden();

        assertDatabaseHas('categories', ['id' => $category->id]);
    });

    test('forbidden access without correct ability', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, []);

        getJson(route('api.categories.index'))
            ->assertStatus(403);
    });
});
