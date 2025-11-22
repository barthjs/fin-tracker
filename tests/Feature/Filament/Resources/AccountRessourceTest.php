<?php

declare(strict_types=1);

use App\Filament\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Resources\Accounts\RelationManagers\TradesRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\TransactionsRelationManager;
use App\Models\Account;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function () {
    $accounts = Account::factory()->count(3)->create();

    livewire(ListAccounts::class)
        ->assertOk()
        ->assertCanSeeTableRecords($accounts);
});

it('renders the view page', function () {
    $account = Account::factory()->create();

    livewire(ViewAccount::class, [
        'record' => $account->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'balance' => $account->balance,
            'description' => $account->description,
        ], 'infolist');
});

it('can load the transactions relation manager', function () {
    $account = Account::factory()->create();

    livewire(TransactionsRelationManager::class, [
        'ownerRecord' => $account,
        'pageClass' => ViewAccount::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($account->transactions);
});

it('can load the trades relation manager', function () {
    $account = Account::factory()->create();

    livewire(TradesRelationManager::class, [
        'ownerRecord' => $account,
        'pageClass' => ViewAccount::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($account->trades);
});
