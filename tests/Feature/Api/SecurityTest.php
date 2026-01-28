<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\SecurityType;
use App\Models\Security;
use App\Models\Trade;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(fn () => asUser());

describe('Security API', function () {
    test('index returns a list of securities', function () {
        $user = User::firstOrFail();
        Security::factory()->count(3)->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        Security::factory()->create(['user_id' => $anotherUser->id]);

        actingAsWithAbilities($user, ApiAbility::SECURITY->all());

        getJson(route('api.securities.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'isin',
                        'symbol',
                        'type',
                        'price',
                        'total_quantity',
                        'market_value',
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

    test('index can filter securities by name', function () {
        $user = User::firstOrFail();
        Security::factory()->create(['name' => 'Apple Inc.', 'user_id' => $user->id]);
        Security::factory()->create(['name' => 'Microsoft Corp.', 'user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::SECURITY->all());

        getJson(route('api.securities.index', ['filter[name]' => 'Apple Inc.']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Apple Inc.')
            ->assertJsonMissing(['name' => 'Microsoft Corp.']);
    });

    test('store creates a new security', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, ApiAbility::SECURITY->all());

        $data = [
            'name' => 'New Security',
            'isin' => 'US0000000000',
            'symbol' => 'NEWS',
            'type' => SecurityType::Stock->value,
            'price' => 150.50,
            'description' => 'A new test security',
            'color' => '#ff0000',
            'is_active' => true,
        ];

        postJson(route('api.securities.store'), $data)
            ->assertCreated()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.isin', $data['isin'])
            ->assertJsonPath('data.symbol', $data['symbol'])
            ->assertJsonPath('data.type', $data['type'])
            ->assertJsonPath('data.price', $data['price'])
            ->assertJsonPath('data.total_quantity', 0)
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.color', $data['color'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('securities', array_merge($data, ['user_id' => $user->id]));
    });

    test('show returns a single security', function () {
        $user = User::firstOrFail();
        $security = Security::factory()->create(['user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::SECURITY->all());

        getJson(route('api.securities.show', $security))
            ->assertOk()
            ->assertJsonPath('data.id', $security->id)
            ->assertJsonPath('data.name', $security->name)
            ->assertJsonPath('data.isin', $security->isin)
            ->assertJsonPath('data.symbol', $security->symbol)
            ->assertJsonPath('data.type', $security->type->value)
            ->assertJsonPath('data.price', $security->price)
            ->assertJsonPath('data.total_quantity', 0)
            ->assertJsonPath('data.description', $security->description)
            ->assertJsonPath('data.color', $security->color)
            ->assertJsonPath('data.is_active', $security->is_active);
    });

    test('update modifies an existing security', function () {
        $user = User::firstOrFail();
        $security = Security::factory()->create(['name' => 'Old Name', 'user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::SECURITY->all());

        $data = [
            'name' => 'Updated Name',
            'isin' => 'DE0000000000',
            'symbol' => 'UPDT',
            'type' => SecurityType::ETF->value,
            'price' => 200.75,
            'description' => 'Updated description',
            'color' => '#00ff00',
            'is_active' => false,
        ];

        putJson(route('api.securities.update', $security), $data)
            ->assertOk()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.isin', $data['isin'])
            ->assertJsonPath('data.symbol', $data['symbol'])
            ->assertJsonPath('data.type', $data['type'])
            ->assertJsonPath('data.price', $data['price'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.color', $data['color'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('securities', array_merge($data, ['id' => $security->id]));
    });

    test('destroy deletes a security', function () {
        $user = User::firstOrFail();
        $security = Security::factory()->create(['user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::SECURITY->all());

        deleteJson(route('api.securities.destroy', $security))
            ->assertNoContent();

        assertDatabaseMissing('securities', ['id' => $security->id]);
    });

    test('destroy fails if security has trades', function () {
        $user = User::firstOrFail();
        $security = Security::factory()->create(['user_id' => $user->id]);
        Trade::factory()->create(['security_id' => $security->id]);

        actingAsWithAbilities($user, ApiAbility::SECURITY->all());

        deleteJson(route('api.securities.destroy', $security))
            ->assertForbidden();

        assertDatabaseHas('securities', ['id' => $security->id]);
    });

    test('forbidden access without correct ability', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, []);

        getJson(route('api.securities.index'))
            ->assertStatus(403);
    });
});
