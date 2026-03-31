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

beforeEach(function () {
    $user = User::factory()->verified()->create();
    $this->user = $user;
});

describe('Category API', function () {
    test('index returns a list of categories', function () {
        actingAsWithAbilities($this->user, ApiAbility::CATEGORY->all());

        Category::factory()->count(3)->create(['user_id' => $this->user->id]);

        $anotherUser = User::factory()->create();
        Category::factory()->create(['user_id' => $anotherUser->id]);

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
        actingAsWithAbilities($this->user, ApiAbility::CATEGORY->all());

        $matchingCategory = Category::factory()->create(['user_id' => $this->user->id]);
        $nonMatchingCategory = Category::factory()->create(['user_id' => $this->user->id]);

        getJson(route('api.categories.index', ['filter[name]' => $matchingCategory->name]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $matchingCategory->name)
            ->assertJsonMissing(['name' => $nonMatchingCategory->name]);
    });

    test('store creates a new category', function () {
        actingAsWithAbilities($this->user, ApiAbility::CATEGORY->all());

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

        assertDatabaseHas('categories', array_merge($data, ['user_id' => $this->user->id]));
    });

    test('store fails with invalid data', function () {
        actingAsWithAbilities($this->user, ApiAbility::CATEGORY->all());

        postJson(route('api.categories.store'), ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'group']);
    });

    test('show returns a single category', function () {
        actingAsWithAbilities($this->user, ApiAbility::CATEGORY->all());

        $category = Category::factory()->create(['user_id' => $this->user->id]);

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
        actingAsWithAbilities($this->user, ApiAbility::CATEGORY->all());

        $category = Category::factory()->create([
            'name' => 'Old Name',
            'group' => CategoryGroup::VarExpenses->value,
            'user_id' => $this->user->id,
        ]);

        $data = [
            'name' => 'Updated Name',
            'group' => CategoryGroup::FixRevenues->value,
            'color' => '#000000',
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
        actingAsWithAbilities($this->user, ApiAbility::CATEGORY->all());

        $category = Category::factory()->create(['user_id' => $this->user->id]);

        deleteJson(route('api.categories.destroy', $category))
            ->assertNoContent();

        assertDatabaseMissing('categories', ['id' => $category->id]);
    });

    test('destroy fails if category has transactions', function () {
        actingAsWithAbilities($this->user, ApiAbility::CATEGORY->all());

        $category = Category::factory()->create(['user_id' => $this->user->id]);
        Transaction::factory()->create(['category_id' => $category->id]);

        deleteJson(route('api.categories.destroy', $category))
            ->assertForbidden();

        assertDatabaseHas('categories', ['id' => $category->id]);
    });

    test('forbidden access without correct ability', function () {
        actingAsWithAbilities($this->user);

        getJson(route('api.categories.index'))
            ->assertStatus(403);
    });
});
