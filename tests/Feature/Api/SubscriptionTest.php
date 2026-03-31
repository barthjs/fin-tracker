<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\PeriodUnit;
use App\Models\Account;
use App\Models\Category;
use App\Models\Subscription;
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

describe('Subscription API', function () {
    test('index returns a list of subscriptions', function () {
        actingAsWithAbilities($this->user, ApiAbility::SUBSCRIPTION->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        Subscription::factory()->count(3)->create(['account_id' => $account->id, 'category_id' => $category->id]);

        $anotherUser = User::factory()->create();
        $account = Account::factory()->create(['user_id' => $anotherUser->id]);
        $category = Category::factory()->create(['user_id' => $anotherUser->id]);
        Subscription::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);

        getJson(route('api.subscriptions.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'created_at',
                        'updated_at',
                        'account_id',
                        'category_id',
                        'name',
                        'description',
                        'amount',
                        'period_unit',
                        'period_frequency',
                        'started_at',
                        'next_payment_date',
                        'ended_at',
                        'auto_generate_transaction',
                        'last_generated_at',
                        'remind_before_payment',
                        'reminder_days_before',
                        'last_reminded_at',
                        'logo',
                        'color',
                        'is_active',
                    ],
                ],
                'links',
                'meta',
            ]);
    });

    test('index can filter subscriptions by name', function () {
        actingAsWithAbilities($this->user, ApiAbility::SUBSCRIPTION->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $matchingSubscription = Subscription::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);
        $nonMatchingSubscription = Subscription::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);

        getJson(route('api.subscriptions.index', ['filter[name]' => $matchingSubscription->name]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', $matchingSubscription->name)
            ->assertJsonMissing(['name' => $nonMatchingSubscription->name]);
    });

    test('store creates a new subscription', function () {
        actingAsWithAbilities($this->user, ApiAbility::SUBSCRIPTION->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'name' => 'Netflix',
            'description' => 'Monthly streaming subscription',
            'amount' => 9.99,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'period_unit' => PeriodUnit::Month->value,
            'period_frequency' => 1,
            'started_at' => now()->startOfDay()->toDateTimeString(),
            'next_payment_date' => now()->addMonth()->toDateTimeString(),
            'auto_generate_transaction' => true,
            'color' => '#ff0000',
            'is_active' => true,
        ];

        postJson(route('api.subscriptions.store'), $data)
            ->assertCreated()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.amount', $data['amount'])
            ->assertJsonPath('data.account_id', $data['account_id'])
            ->assertJsonPath('data.category_id', $data['category_id'])
            ->assertJsonPath('data.period_unit', $data['period_unit'])
            ->assertJsonPath('data.period_frequency', $data['period_frequency'])
            ->assertJsonPath('data.auto_generate_transaction', $data['auto_generate_transaction'])
            ->assertJsonPath('data.color', $data['color'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('subscriptions', [
            'account_id' => $data['account_id'],
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'amount' => $data['amount'],
        ]);
    });

    test('store fails with invalid data', function () {
        actingAsWithAbilities($this->user, ApiAbility::SUBSCRIPTION->all());

        postJson(route('api.subscriptions.store'), ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'amount', 'account_id', 'category_id', 'period_unit', 'started_at']);
    });

    test('show returns a single subscription', function () {
        actingAsWithAbilities($this->user, ApiAbility::SUBSCRIPTION->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $subscription = Subscription::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);

        getJson(route('api.subscriptions.show', $subscription))
            ->assertOk()
            ->assertJsonPath('data.id', $subscription->id)
            ->assertJsonPath('data.account_id', $subscription->account_id)
            ->assertJsonPath('data.category_id', $subscription->category_id)
            ->assertJsonPath('data.name', $subscription->name)
            ->assertJsonPath('data.amount', $subscription->amount)
            ->assertJsonPath('data.period_unit', $subscription->period_unit->value)
            ->assertJsonPath('data.period_frequency', $subscription->period_frequency)
            ->assertJsonPath('data.is_active', $subscription->is_active);
    });

    test('update modifies an existing subscription', function () {
        actingAsWithAbilities($this->user, ApiAbility::SUBSCRIPTION->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $subscription = Subscription::factory()->create([
            'name' => 'Old Name',
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 5.00,
        ]);

        $data = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'amount' => 12.99,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'period_unit' => PeriodUnit::Year->value,
            'period_frequency' => 1,
            'started_at' => $subscription->started_at->toDateString(),
            'next_payment_date' => $subscription->next_payment_date->toDateString(),
            'auto_generate_transaction' => false,
            'remind_before_payment' => false,
            'reminder_days_before' => 5,
            'color' => '#00ff00',
            'is_active' => false,
        ];

        putJson(route('api.subscriptions.update', $subscription), $data)
            ->assertOk()
            ->assertJsonPath('data.name', $data['name'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.amount', $data['amount'])
            ->assertJsonPath('data.period_unit', $data['period_unit'])
            ->assertJsonPath('data.auto_generate_transaction', $data['auto_generate_transaction'])
            ->assertJsonPath('data.remind_before_payment', $data['remind_before_payment'])
            ->assertJsonPath('data.is_active', $data['is_active']);

        assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'name' => $data['name'],
            'amount' => $data['amount'],
        ]);
    });

    test('destroy deletes a subscription', function () {
        actingAsWithAbilities($this->user, ApiAbility::SUBSCRIPTION->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $subscription = Subscription::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);

        deleteJson(route('api.subscriptions.destroy', $subscription))
            ->assertNoContent();

        assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
    });

    test('forbidden access without correct ability', function () {
        actingAsWithAbilities($this->user, []);

        getJson(route('api.subscriptions.index'))
            ->assertStatus(403);
    });
});
