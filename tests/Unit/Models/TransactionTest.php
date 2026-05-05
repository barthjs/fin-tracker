<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

it('returns the owner of the transaction', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $transaction = Transaction::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]);

    expect($transaction->user->id)->toBe(auth()->user()->id);
});
it('trims the payee when creating', function (): void {
    $transaction = Transaction::factory()->create(['payee' => '  Supermarket  ']);

    expect($transaction->payee)->toBe('Supermarket');
});

it('keeps the payee null when none is given', function (): void {
    $transaction = Transaction::factory()->create(['payee' => null]);

    expect($transaction->payee)->toBeNull();
});

it('nullifies the transfer account when the type is not a transfer', function (): void {
    $account = Account::factory()->create();
    $transferAccount = Account::factory()->create();

    $transaction = Transaction::factory()->create([
        'type' => TransactionType::Expense,
        'account_id' => $account->id,
        'transfer_account_id' => $transferAccount->id,
    ]);

    expect($transaction->transfer_account_id)->toBeNull();
});

it('keeps the transfer account when the type is a transfer', function (): void {
    $account = Account::factory()->create();
    $transferAccount = Account::factory()->create();

    $transaction = Transaction::factory()->create([
        'type' => TransactionType::Transfer,
        'account_id' => $account->id,
        'transfer_account_id' => $transferAccount->id,
    ]);

    expect($transaction->transfer_account_id)->toBe($transferAccount->id);
});

it('falls back to a default account when none is given', function (): void {
    $category = Category::factory()->create();

    $transaction = Transaction::factory()->create([
        'account_id' => null,
        'category_id' => $category->id,
    ]);

    assertDatabaseHas('accounts', ['id' => $transaction->account_id, 'name' => 'Demo']);
});

it('falls back to a default category when none is given', function (): void {
    $account = Account::factory()->create();

    $transaction = Transaction::factory()->create([
        'account_id' => $account->id,
        'category_id' => null,
    ]);

    assertDatabaseHas('categories', ['id' => $transaction->category_id, 'name' => 'Demo']);
});

it('trims the payee when updating', function (): void {
    $transaction = Transaction::factory()->create(['payee' => 'Supermarket']);

    $transaction->update(['payee' => '  Bakery  ']);

    expect($transaction->fresh()?->payee)->toBe('Bakery');
});

it('nullifies the transfer account on update when the type is no longer a transfer', function (): void {
    $account = Account::factory()->create();
    $transferAccount = Account::factory()->create();

    $transaction = Transaction::factory()->create([
        'type' => TransactionType::Transfer,
        'account_id' => $account->id,
        'transfer_account_id' => $transferAccount->id,
    ]);
    expect($transaction->transfer_account_id)->toBe($transferAccount->id);

    $transaction->update(['type' => TransactionType::Expense]);

    expect($transaction->fresh()?->transfer_account_id)->toBeNull();
});
