<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\Currency;
use App\Models\Portfolio;
use App\Models\Trade;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(fn () => asUser());

describe('Portfolio API', function () {
    test('index returns a list of portfolios', function () {
        $user = User::firstOrFail();
        Portfolio::factory()->count(3)->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        Portfolio::factory()->create(['user_id' => $anotherUser->id]);

        actingAsWithAbilities($user, ApiAbility::PORTFOLIO->all());

        getJson(route('api.portfolios.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'market_value',
                        'currency',
                        'description',
                        'logo',
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

    test('index can filter portfolios by name', function () {
        $user = User::firstOrFail();
        Portfolio::factory()->create(['name' => 'Main Portfolio', 'user_id' => $user->id]);
        Portfolio::factory()->create(['name' => 'Secondary Portfolio', 'user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::PORTFOLIO->all());

        getJson(route('api.portfolios.index', ['filter[name]' => 'Main Portfolio']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Main Portfolio')
            ->assertJsonMissing(['name' => 'Secondary Portfolio']);
    });

    test('store creates a new portfolio', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, ApiAbility::PORTFOLIO->all());

        $data = [
            'name' => 'New Portfolio',
            'currency' => Currency::EUR->value,
            'description' => 'A new test portfolio',
            'color' => '#ff0000',
            'is_active' => true,
        ];

        postJson(route('api.portfolios.store'), $data)
            ->assertCreated()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.currency', $data['currency'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.color', $data['color'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('portfolios', array_merge($data, ['user_id' => $user->id]));
    });

    test('show returns a single portfolio', function () {
        $user = User::firstOrFail();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::PORTFOLIO->all());

        getJson(route('api.portfolios.show', $portfolio))
            ->assertOk()
            ->assertJsonPath('data.id', $portfolio->id)
            ->assertJsonPath('data.name', $portfolio->name)
            ->assertJsonPath('data.market_value', 0)
            ->assertJsonPath('data.currency', $portfolio->currency->value)
            ->assertJsonPath('data.description', $portfolio->description)
            ->assertJsonPath('data.color', $portfolio->color)
            ->assertJsonPath('data.is_active', $portfolio->is_active);
    });

    test('update modifies an existing portfolio', function () {
        $user = User::firstOrFail();
        $portfolio = Portfolio::factory()->create(['name' => 'Old Name', 'user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::PORTFOLIO->all());

        $data = [
            'name' => 'Updated Name',
            'currency' => Currency::USD->value,
            'description' => 'Updated description',
            'color' => '#00ff00',
            'is_active' => false,
        ];

        putJson(route('api.portfolios.update', $portfolio), $data)
            ->assertOk()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.currency', $data['currency'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.color', $data['color'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('portfolios', array_merge($data, ['id' => $portfolio->id]));
    });

    test('destroy deletes a portfolio', function () {
        $user = User::firstOrFail();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::PORTFOLIO->all());

        deleteJson(route('api.portfolios.destroy', $portfolio))
            ->assertNoContent();

        assertDatabaseMissing('portfolios', ['id' => $portfolio->id]);
    });

    test('destroy fails if portfolio has trades', function () {
        $user = User::firstOrFail();
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
        Trade::factory()->create(['portfolio_id' => $portfolio->id]);

        actingAsWithAbilities($user, ApiAbility::PORTFOLIO->all());

        deleteJson(route('api.portfolios.destroy', $portfolio))
            ->assertForbidden();

        assertDatabaseHas('portfolios', ['id' => $portfolio->id]);
    });

    test('forbidden access without correct ability', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, []);

        getJson(route('api.portfolios.index'))
            ->assertStatus(403);
    });
});
