<?php

declare(strict_types=1);

use App\Enums\TradeType;
use App\Filament\Resources\Trades\Pages\ListTrades;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function (): void {
    $account = Account::factory()->create();
    $security = Security::factory()->create();

    $trades = Trade::factory()
        ->count(3)
        ->create([
            'account_id' => $account->id,
            'security_id' => $security->id,
        ]);

    livewire(ListTrades::class)
        ->assertOk()
        ->assertCanSeeTableRecords($trades)
        ->assertActionExists('import')
        ->assertActionExists('export');
});

it('scopes trades to the current user and filters by account, portfolio, security, date and type', function (): void {
    $accountA = Account::factory()->create();
    $accountB = Account::factory()->create();
    $portfolioA = Portfolio::factory()->create();
    $portfolioB = Portfolio::factory()->create();
    $securityA = Security::factory()->create();
    $securityB = Security::factory()->create();

    $buy = Trade::factory()->create([
        'type' => TradeType::Buy,
        'date_time' => now()->subDays(2),
        'account_id' => $accountA->id,
        'portfolio_id' => $portfolioA->id,
        'security_id' => $securityA->id,
    ]);
    $sell = Trade::factory()->create([
        'type' => TradeType::Sell,
        'date_time' => now()->subDays(10),
        'account_id' => $accountB->id,
        'portfolio_id' => $portfolioB->id,
        'security_id' => $securityB->id,
    ]);

    $other = User::factory()->create();
    $otherTrade = Trade::factory()->create([
        'account_id' => Account::factory()->create(['user_id' => $other->id])->id,
        'portfolio_id' => Portfolio::factory()->create(['user_id' => $other->id])->id,
        'security_id' => Security::factory()->create(['user_id' => $other->id])->id,
    ]);

    livewire(ListTrades::class)
        ->assertCanSeeTableRecords([$buy, $sell])
        ->assertCanNotSeeTableRecords([$otherTrade])
        ->filterTable('account_id', $accountA)
        ->assertCanSeeTableRecords([$buy])
        ->assertCanNotSeeTableRecords([$sell])
        ->resetTableFilters()
        ->filterTable('portfolio_id', $portfolioB)
        ->assertCanSeeTableRecords([$sell])
        ->assertCanNotSeeTableRecords([$buy])
        ->resetTableFilters()
        ->filterTable('security_id', $securityA)
        ->assertCanSeeTableRecords([$buy])
        ->assertCanNotSeeTableRecords([$sell])
        ->resetTableFilters()
        ->filterTable('date_range', ['from' => now()->subDays(5)->toDateString(), 'until' => now()->toDateString()])
        ->assertCanSeeTableRecords([$buy])
        ->assertCanNotSeeTableRecords([$sell])
        ->resetTableFilters()
        ->set('activeTab', TradeType::Buy->value)
        ->assertCanSeeTableRecords([$buy])
        ->assertCanNotSeeTableRecords([$sell])
        ->set('activeTab', TradeType::Sell->value)
        ->assertCanSeeTableRecords([$sell])
        ->assertCanNotSeeTableRecords([$buy]);
});
it('can create a trade', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create();

    $data = Trade::factory()->make([
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ])->toArray();
    $data['date_time'] = now()->startOfMinute()->toDateTimeString();

    livewire(ListTrades::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('trades', $data);
});

it('can edit a trade', function (): void {
    $trade = Trade::factory()->create();

    $data = Trade::factory()->make([
        'account_id' => $trade->account_id,
        'portfolio_id' => $trade->portfolio_id,
        'security_id' => $trade->security_id,
    ])->toArray();
    $data['date_time'] = now()->startOfMinute()->toDateTimeString();

    livewire(ListTrades::class, ['record' => $trade->id])
        ->callAction(
            TestAction::make('edit')->table($trade),
            $data
        )
        ->assertHasNoFormErrors();

    assertDatabaseHas('trades', array_merge(['id' => $trade->id], $data));
});

it('can delete a trade', function (): void {
    $trade = Trade::factory()->create();

    livewire(ListTrades::class)
        ->callAction(TestAction::make('delete')->table($trade));

    assertModelMissing($trade);
});

it('can bulk edit the account of trades', function (): void {
    $accountA = Account::factory()->create();
    $accountB = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create();

    $trades = Trade::factory()->count(2)->create([
        'account_id' => $accountA->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);

    livewire(ListTrades::class)
        ->callTableBulkAction('account_id', $trades, ['account_id' => $accountB->id]);

    foreach ($trades as $trade) {
        $this->assertDatabaseHas('trades', ['id' => $trade->id, 'account_id' => $accountB->id]);
    }
});

it('can bulk edit the portfolio of trades', function (): void {
    $account = Account::factory()->create();
    $portfolioA = Portfolio::factory()->create();
    $portfolioB = Portfolio::factory()->create();
    $security = Security::factory()->create();

    $trades = Trade::factory()->count(2)->create([
        'account_id' => $account->id,
        'portfolio_id' => $portfolioA->id,
        'security_id' => $security->id,
    ]);

    livewire(ListTrades::class)
        ->callTableBulkAction('portfolio_id', $trades, ['portfolio_id' => $portfolioB->id]);

    foreach ($trades as $trade) {
        $this->assertDatabaseHas('trades', ['id' => $trade->id, 'portfolio_id' => $portfolioB->id]);
    }
});
it('can bulk delete trades', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create();

    $trades = Trade::factory()->count(3)->create([
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ]);

    livewire(ListTrades::class)
        ->callTableBulkAction('delete', $trades);

    foreach ($trades as $trade) {
        $this->assertModelMissing($trade);
    }
});

it('computes the total amount for a sell trade via the form', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create();

    livewire(ListTrades::class)
        ->mountAction('create')
        ->setActionData([
            'account_id' => $account->id,
            'portfolio_id' => $portfolio->id,
            'security_id' => $security->id,
            'type' => TradeType::Sell->value,
            'quantity' => '10',
            'price' => '15',
            'tax' => '2',
            'fee' => '1',
        ])
        ->assertActionDataSet([
            // Sell: price * quantity - (tax + fee) = 15*10 - 3 = 147
            'total_amount' => 147.0,
        ]);
});

it('recalculates balances, market value and quantity when creating a trade', function (): void {
    $account = Account::factory()->create();
    $portfolio = Portfolio::factory()->create();
    $security = Security::factory()->create(['price' => 10.0]);

    $data = [
        'date_time' => now()->startOfMinute()->toDateTimeString(),
        'type' => TradeType::Buy->value,
        'quantity' => 5,
        'price' => 10,
        'tax' => 0,
        'fee' => 0,
        'account_id' => $account->id,
        'portfolio_id' => $portfolio->id,
        'security_id' => $security->id,
    ];

    livewire(ListTrades::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    expect($account->fresh()?->balance)->toBe(-50.0)
        ->and($portfolio->fresh()?->market_value)->toBe(50.0)
        ->and($security->fresh()?->total_quantity)->toBe(5.0);
});
