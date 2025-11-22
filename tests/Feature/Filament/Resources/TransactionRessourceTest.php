<?php

declare(strict_types=1);

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;

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
