<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Enums\TradeType;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function () {
    asUser();
});

describe('Trade API', tests: function () {
    test('index returns a list of trades', function () {
        $user = User::firstOrFail();
        Trade::factory()->count(3)->create();

        actingAsWithAbilities($user, ApiAbility::TRADE->all());

        getJson(route('api.trades.index'))
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'date_time',
                        'type',
                        'total_amount',
                        'quantity',
                        'price',
                        'fee',
                        'tax',
                        'notes',
                        'account_id',
                        'portfolio_id',
                        'security_id',
                    ],
                ],
            ]);
    });

    test('store creates a new trade', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, ApiAbility::TRADE->all());

        $account = Account::factory()->create(['user_id' => $user->id]);
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
        $security = Security::factory()->create(['price' => 100, 'user_id' => $user->id]);

        $data = [
            'date_time' => now()->toDateTimeString(),
            'type' => TradeType::Buy->value,
            'quantity' => 10,
            'price' => 100,
            'fee' => 5,
            'tax' => 2,
            'notes' => 'Test',
            'account_id' => $account->id,
            'portfolio_id' => $portfolio->id,
            'security_id' => $security->id,
        ];

        postJson(route('api.trades.store'), $data)
            ->assertCreated()
            ->assertJsonPath('data.quantity', $data['quantity'])
            ->assertJsonPath('data.price', $data['price'])
            ->assertJsonPath('data.fee', $data['fee'])
            ->assertJsonPath('data.tax', $data['tax'])
            ->assertJsonPath('data.notes', $data['notes'])
            ->assertJsonPath('data.account_id', $account->id)
            ->assertJsonPath('data.portfolio_id', $portfolio->id)
            ->assertJsonPath('data.security_id', $security->id);

        assertDatabaseHas('trades', $data);

        $trade = Trade::where('account_id', $account->id)->firstOrFail();
        $this->assertEquals(1007, $trade->total_amount);

        $this->assertEquals(-1007, $account->fresh()?->balance);
        $this->assertEquals(1000, $portfolio->fresh()?->market_value);
        $this->assertEquals(10, $security->fresh()?->total_quantity);
    });

    test('update modifies an existing trade', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, ApiAbility::TRADE->all());

        $account = Account::factory()->create(['user_id' => $user->id]);
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
        $security = Security::factory()->create(['price' => 100, 'user_id' => $user->id]);

        $trade = Trade::factory()->create([
            'type' => TradeType::Buy,
            'quantity' => 10,
            'price' => 100,
            'tax' => 0,
            'fee' => 0,
            'account_id' => $account->id,
            'portfolio_id' => $portfolio->id,
            'security_id' => $security->id,
        ]);

        $data = [
            'date_time' => $trade->date_time->toDateTimeString(),
            'type' => TradeType::Buy->value,
            'quantity' => 20,
            'price' => 100,
            'tax' => 1,
            'fee' => 1,
            'notes' => 'Test',
            'account_id' => $account->id,
            'portfolio_id' => $portfolio->id,
            'security_id' => $security->id,
        ];

        putJson(route('api.trades.update', $trade), $data)
            ->assertOk()
            ->assertJsonPath('data.total_amount', 2002)
            ->assertJsonPath('data.quantity', $data['quantity'])
            ->assertJsonPath('data.price', $data['price'])
            ->assertJsonPath('data.tax', $data['tax'])
            ->assertJsonPath('data.fee', $data['fee'])
            ->assertJsonPath('data.notes', $data['notes'])
            ->assertJsonPath('data.account_id', $account->id)
            ->assertJsonPath('data.portfolio_id', $portfolio->id)
            ->assertJsonPath('data.security_id', $security->id);

        assertDatabaseHas('trades', $data);

        $this->assertEquals(-2002, $account->fresh()?->balance);
        $this->assertEquals(2000, $portfolio->fresh()?->market_value);
        $this->assertEquals(20, $security->fresh()?->total_quantity);
    });

    test('destroy deletes a trade', function () {
        $user = User::firstOrFail();
        actingAsWithAbilities($user, ApiAbility::TRADE->all());

        $account = Account::factory()->create(['user_id' => $user->id]);
        $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
        $security = Security::factory()->create(['price' => 100, 'user_id' => $user->id]);

        $trade = Trade::factory()->create([
            'type' => TradeType::Buy,
            'quantity' => 10,
            'price' => 100,
            'fee' => 0,
            'tax' => 0,
            'account_id' => $account->id,
            'portfolio_id' => $portfolio->id,
            'security_id' => $security->id,
        ]);

        deleteJson(route('api.trades.destroy', $trade))
            ->assertNoContent();

        assertDatabaseMissing('trades', ['id' => $trade->id]);
        $this->assertEquals(0, $account->fresh()?->balance);
        $this->assertEquals(0, $portfolio->fresh()?->market_value);
        $this->assertEquals(0, $security->fresh()?->total_quantity);
    });
});
