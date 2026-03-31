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

beforeEach(function () {
    $user = User::factory()->verified()->create();
    $this->user = $user;
});

describe('Portfolio API', function () {
    test('index returns a list of portfolios', function () {
        actingAsWithAbilities($this->user, ApiAbility::PORTFOLIO->all());

        Portfolio::factory()->count(3)->create(['user_id' => $this->user->id]);

        $anotherUser = User::factory()->create();
        Portfolio::factory()->create(['user_id' => $anotherUser->id]);

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
        actingAsWithAbilities($this->user, ApiAbility::PORTFOLIO->all());

        $matchingPortfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);
        $nonMatchingPortfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

        getJson(route('api.portfolios.index', ['filter[name]' => $matchingPortfolio->name]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $matchingPortfolio->name)
            ->assertJsonMissing(['name' => $nonMatchingPortfolio->name]);
    });

    test('store creates a new portfolio', function () {
        actingAsWithAbilities($this->user, ApiAbility::PORTFOLIO->all());

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

        assertDatabaseHas('portfolios', array_merge($data, ['user_id' => $this->user->id]));
    });

    test('store fails with invalid data', function () {
        actingAsWithAbilities($this->user, ApiAbility::PORTFOLIO->all());

        postJson(route('api.portfolios.store'), ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'currency']);
    });

    test('show returns a single portfolio', function () {
        actingAsWithAbilities($this->user, ApiAbility::PORTFOLIO->all());

        $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

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
        actingAsWithAbilities($this->user, ApiAbility::PORTFOLIO->all());

        $portfolio = Portfolio::factory()->create([
            'name' => 'Old Name',
            'currency' => Currency::EUR,
            'user_id' => $this->user->id,
        ]);

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
        actingAsWithAbilities($this->user, ApiAbility::PORTFOLIO->all());

        $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);

        deleteJson(route('api.portfolios.destroy', $portfolio))
            ->assertNoContent();

        assertDatabaseMissing('portfolios', ['id' => $portfolio->id]);
    });

    test('destroy fails if portfolio has trades', function () {
        actingAsWithAbilities($this->user, ApiAbility::PORTFOLIO->all());

        $portfolio = Portfolio::factory()->create(['user_id' => $this->user->id]);
        Trade::factory()->create(['portfolio_id' => $portfolio->id]);

        deleteJson(route('api.portfolios.destroy', $portfolio))
            ->assertForbidden();

        assertDatabaseHas('portfolios', ['id' => $portfolio->id]);
    });

    test('forbidden access without correct ability', function () {
        actingAsWithAbilities($this->user);

        getJson(route('api.portfolios.index'))
            ->assertStatus(403);
    });
});
