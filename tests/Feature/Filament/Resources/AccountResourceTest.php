<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Filament\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Resources\Accounts\RelationManagers\SubscriptionsRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\TradesRelationManager;
use App\Filament\Resources\Accounts\RelationManagers\TransactionsRelationManager;
use App\Models\Account;
use App\Models\Category;
use App\Models\Subscription;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function (): void {
    $accounts = Account::factory()->count(3)->create();

    livewire(ListAccounts::class)
        ->assertOk()
        ->assertCanSeeTableRecords($accounts)
        ->assertActionExists('import')
        ->assertActionExists('export');
});

it('can filter accounts by inactivity', function (): void {
    $activeAccount = Account::factory()->create(['is_active' => true]);
    $inactiveAccount = Account::factory()->create(['is_active' => false]);

    livewire(ListAccounts::class)
        ->assertCanSeeTableRecords([$activeAccount])
        ->assertCanNotSeeTableRecords([$inactiveAccount])
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactiveAccount])
        ->assertCanNotSeeTableRecords([$activeAccount]);
});

it('can create an account', function (): void {
    $data = Account::factory()->make()->toArray();

    livewire(ListAccounts::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('accounts', $data);
});

it('renders the view page', function (): void {
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

it('can edit an account', function (): void {
    $account = Account::factory()->create();
    $data = Account::factory()->make()->toArray();

    livewire(ViewAccount::class, ['record' => $account->id])
        ->callAction('edit', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('accounts', array_merge(['id' => $account->id], $data));
});

it('can delete an account', function (): void {
    $account = Account::factory()->create();

    livewire(ViewAccount::class, ['record' => $account->id])
        ->callAction('delete')
        ->assertHasNoActionErrors();

    assertModelMissing($account);
});

it('can load the transactions relation manager', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    livewire(TransactionsRelationManager::class, [
        'ownerRecord' => $account,
        'pageClass' => ViewAccount::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($account->transactions)
        ->callAction(TestAction::make('create')->table(), [
            'date_time' => now()->startOfMinute()->toDateTimeString(),
            'type' => TransactionType::Expense->value,
            'amount' => 50,
            'payee' => 'Shop',
            'category_id' => $category->id,
        ])
        ->assertHasNoFormErrors();
});

it('can load the trades relation manager', function (): void {
    $account = Account::factory()->create();

    livewire(TradesRelationManager::class, [
        'ownerRecord' => $account,
        'pageClass' => ViewAccount::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($account->trades);
});

it('can load the subscriptions relation manager', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $subscription = Subscription::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);

    livewire(SubscriptionsRelationManager::class, [
        'ownerRecord' => $account,
        'pageClass' => ViewAccount::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords([$subscription]);
});
