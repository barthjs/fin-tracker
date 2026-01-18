<?php

declare(strict_types=1);

use App\Filament\Resources\Trades\Pages\ListTrades;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Trade;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function () {
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
        ->assertCanSeeTableRecords($trades);
});

it('can create a trade', function () {
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

    $this->assertDatabaseHas('trades', $data);
});

it('can edit a trade', function () {
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

    $this->assertDatabaseHas('trades', array_merge(['id' => $trade->id], $data));
});
