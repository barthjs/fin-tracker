<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\Currency;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(fn () => asUser());

describe('Account API', function () {
    test('index returns a list of accounts', function () {
        $user = User::firstOrFail();
        Account::factory()->count(3)->create(['user_id' => $user->id]);

        $anotherUser = User::factory()->create();
        Account::factory()->create(['user_id' => $anotherUser->id]);

        actingAsWithAbilities($user, ApiAbility::ACCOUNT->all());

        getJson(route('api.accounts.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'balance',
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

    test('index can filter accounts by name', function () {
        $user = User::firstOrFail();
        Account::factory()->create(['name' => 'Savings', 'user_id' => $user->id]);
        Account::factory()->create(['name' => 'Checking', 'user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::ACCOUNT->all());

        getJson(route('api.accounts.index', ['filter[name]' => 'Savings']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Savings')
            ->assertJsonMissing(['name' => 'Checking']);
    });

    test('store creates a new account', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, ApiAbility::ACCOUNT->all());

        $data = [
            'name' => 'New Account',
            'currency' => Currency::EUR->value,
            'description' => 'A new test account',
            'color' => '#ffffff',
            'is_active' => true,
        ];

        postJson(route('api.accounts.store'), $data)
            ->assertCreated()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.currency', $data['currency'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.color', $data['color'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('accounts', array_merge($data, ['user_id' => $user->id]));
    });

    test('show returns a single account', function () {
        $user = User::firstOrFail();
        $account = Account::factory()->create(['user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::ACCOUNT->all());

        getJson(route('api.accounts.show', $account))
            ->assertOk()
            ->assertJsonPath('data.id', $account->id)
            ->assertJsonPath('data.name', $account->name)
            ->assertJsonPath('data.balance', 0)
            ->assertJsonPath('data.currency', $account->currency->value)
            ->assertJsonPath('data.description', $account->description)
            ->assertJsonPath('data.color', $account->color)
            ->assertJsonPath('data.is_active', $account->is_active);
    });

    test('update modifies an existing account', function () {
        $user = User::firstOrFail();
        $account = Account::factory()->create([
            'name' => 'Old Name',
            'currency' => Currency::EUR,
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        actingAsWithAbilities($user, ApiAbility::ACCOUNT->all());

        $data = [
            'name' => 'Updated Name',
            'currency' => Currency::USD->value,
            'description' => 'Updated description',
            'color' => '#000000',
            'is_active' => false,
        ];

        putJson(route('api.accounts.update', $account), $data)
            ->assertOk()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.currency', $data['currency'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.color', $data['color'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('accounts', array_merge($data, ['id' => $account->id]));
    });

    test('destroy deletes an account', function () {
        $user = User::firstOrFail();
        $account = Account::factory()->create(['user_id' => $user->id]);

        actingAsWithAbilities($user, ApiAbility::ACCOUNT->all());

        deleteJson(route('api.accounts.destroy', $account))
            ->assertNoContent();

        assertDatabaseMissing('accounts', ['id' => $account->id]);
    });

    test('destroy fails if account has transactions', function () {
        $user = User::firstOrFail();
        $account = Account::factory()->create(['user_id' => $user->id]);
        Transaction::factory()->create(['account_id' => $account->id]);

        actingAsWithAbilities($user, ApiAbility::ACCOUNT->all());

        deleteJson(route('api.accounts.destroy', $account))
            ->assertForbidden();

        assertDatabaseHas('accounts', ['id' => $account->id]);
    });

    test('forbidden access without correct ability', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, []);

        getJson(route('api.accounts.index'))
            ->assertStatus(403);
    });
});
