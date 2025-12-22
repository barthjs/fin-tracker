<?php

declare(strict_types=1);

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $transactions = Transaction::factory()
        ->count(3)
        ->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
        ]);

    livewire(ListTransactions::class)
        ->assertOk()
        ->assertCanSeeTableRecords($transactions);
});

it('can create a transaction', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $data = Transaction::factory()->make([
        'account_id' => $account->id,
        'category_id' => $category->id,
    ])->toArray();
    $data['date_time'] = now()->startOfMinute()->toDateTimeString();

    livewire(ListTransactions::class)
        ->callAction('create', $data)
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('transactions', $data);
});

it('can edit a transaction', function () {
    $transaction = Transaction::factory()->create();

    $data = Transaction::factory()
        ->make([
            'account_id' => $transaction->account_id,
            'category_id' => $transaction->category_id,
        ])
        ->toArray();
    $data['date_time'] = now()->startOfMinute()->toDateTimeString();

    livewire(ListTransactions::class)
        ->callAction(
            TestAction::make('edit')->table($transaction),
            $data
        )
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('transactions', array_merge(['id' => $transaction->id], $data));
});
