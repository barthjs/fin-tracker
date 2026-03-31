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

beforeEach(function () {
    $user = User::factory()->verified()->create();
    $this->user = $user;
});

describe('Account API', function () {
    test('index returns a list of accounts', function () {
        actingAsWithAbilities($this->user, ApiAbility::ACCOUNT->all());

        Account::factory(3)->create(['user_id' => $this->user->id]);

        $anotherUser = User::factory()->create();
        Account::factory()->create(['user_id' => $anotherUser->id]);

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
        actingAsWithAbilities($this->user, ApiAbility::ACCOUNT->all());

        $matchingAccount = Account::factory()->create(['user_id' => $this->user->id]);
        $nonMatchingAccount = Account::factory()->create(['user_id' => $this->user->id]);

        getJson(route('api.accounts.index', ['filter[name]' => $matchingAccount->name]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $matchingAccount->name)
            ->assertJsonMissing(['name' => $nonMatchingAccount->name]);
    });

    test('store creates a new account', function () {
        actingAsWithAbilities($this->user, ApiAbility::ACCOUNT->all());

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

        assertDatabaseHas('accounts', array_merge($data, ['user_id' => $this->user->id]));
    });

    test('store fails with invalid data', function () {
        actingAsWithAbilities($this->user, ApiAbility::ACCOUNT->all());

        postJson(route('api.accounts.store'), ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'currency']);
    });

    test('show returns a single account', function () {
        actingAsWithAbilities($this->user, ApiAbility::ACCOUNT->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);

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
        actingAsWithAbilities($this->user, ApiAbility::ACCOUNT->all());

        $account = Account::factory()->create([
            'name' => 'Old Name',
            'currency' => Currency::EUR,
            'user_id' => $this->user->id,
        ]);

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
        actingAsWithAbilities($this->user, ApiAbility::ACCOUNT->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);

        deleteJson(route('api.accounts.destroy', $account))
            ->assertNoContent();

        assertDatabaseMissing('accounts', ['id' => $account->id]);
    });

    test('destroy fails if account has transactions', function () {
        actingAsWithAbilities($this->user, ApiAbility::ACCOUNT->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        Transaction::factory()->create(['account_id' => $account->id]);

        deleteJson(route('api.accounts.destroy', $account))
            ->assertForbidden();

        assertDatabaseHas('accounts', ['id' => $account->id]);
    });

    test('forbidden access without correct ability', function () {
        actingAsWithAbilities($this->user);

        getJson(route('api.accounts.index'))
            ->assertStatus(403);
    });
});
