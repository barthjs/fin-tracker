<?php

declare(strict_types=1);

use App\Filament\Resources\Trades\Pages\ListTrades;
use App\Models\Account;
use App\Models\Security;
use App\Models\Trade;

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
