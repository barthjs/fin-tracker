<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\TransactionType;
use App\Models\Account;
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

describe('Transaction API', function () {
    test('index returns a list of transactions', function () {
        actingAsWithAbilities($this->user, ApiAbility::TRANSACTION->all());

        Transaction::factory()->count(3)->create();

        getJson(route('api.transactions.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'date_time',
                        'type',
                        'amount',
                        'payee',
                        'notes',
                        'account_id',
                        'transfer_account_id',
                        'category_id',
                    ],
                ],
            ]);
    });

    test('store creates a new transaction', function () {
        actingAsWithAbilities($this->user, ApiAbility::TRANSACTION->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'date_time' => now()->toDateTimeString(),
            'type' => TransactionType::Expense->value,
            'amount' => 100.50,
            'payee' => 'Supermarket',
            'notes' => 'Weekly groceries',
            'account_id' => $account->id,
            'category_id' => $category->id,
        ];

        postJson(route('api.transactions.store'), $data)
            ->assertCreated()
            ->assertJsonPath('data.amount', $data['amount'])
            ->assertJsonPath('data.payee', $data['payee'])
            ->assertJsonPath('data.notes', $data['notes'])
            ->assertJsonPath('data.account_id', $data['account_id'])
            ->assertJsonPath('data.category_id', $category->id);

        assertDatabaseHas('transactions', $data);

        $this->assertEquals(-100.5, $account->fresh()?->balance);
    });

    test('store fails with invalid data', function () {
        actingAsWithAbilities($this->user, ApiAbility::TRANSACTION->all());

        postJson(route('api.transactions.store'), ['amount' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date_time', 'type', 'amount', 'account_id', 'category_id']);
    });

    test('update modifies an existing transaction', function () {
        actingAsWithAbilities($this->user, ApiAbility::TRANSACTION->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $transaction = Transaction::factory()->create([
            'account_id' => $account->id,
            'amount' => 50.0,
            'type' => TransactionType::Expense,
        ]);

        $data = [
            'date_time' => $transaction->date_time->toDateTimeString(),
            'type' => TransactionType::Revenue->value,
            'amount' => 75,
            'payee' => 'Updated Payee',
            'account_id' => $account->id,
            'category_id' => $transaction->category_id,
        ];

        putJson(route('api.transactions.update', $transaction), $data)
            ->assertOk()
            ->assertJsonPath('data.type', $data['type'])
            ->assertJsonPath('data.amount', $data['amount'])
            ->assertJsonPath('data.payee', $data['payee'])
            ->assertJsonPath('data.account_id', $data['account_id'])
            ->assertJsonPath('data.category_id', $data['category_id']);

        assertDatabaseHas('transactions', $data);

        $this->assertEquals(75.0, $account->fresh()?->balance);
    });

    test('destroy deletes a transaction', function () {
        actingAsWithAbilities($this->user, ApiAbility::TRANSACTION->all());

        $account = Account::factory()->create(['user_id' => $this->user->id]);
        $transaction = Transaction::factory()->create([
            'type' => TransactionType::Expense,
            'amount' => 100,
            'account_id' => $account->id,
        ]);

        deleteJson(route('api.transactions.destroy', $transaction))
            ->assertNoContent();

        assertDatabaseMissing('transactions', ['id' => $transaction->id]);
        $this->assertEquals(0.0, $account->fresh()?->balance);
    });

    test('forbidden access without correct ability', function () {
        actingAsWithAbilities($this->user);

        getJson(route('api.transactions.index'))
            ->assertStatus(403);
    });
});
