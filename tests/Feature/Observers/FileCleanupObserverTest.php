<?php

declare(strict_types=1);

use App\Models\Account;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    asUser();

    Storage::fake('public');
});

it('deletes the stored file when the model is deleted', function (): void {
    Storage::disk('public')->put('accounts/logo.png', 'data');
    $account = Account::factory()->create(['logo' => 'accounts/logo.png']);

    $account->delete();

    Storage::disk('public')->assertMissing('accounts/logo.png');
});

it('deletes the previous file when it is replaced on update', function (): void {
    Storage::disk('public')->put('accounts/old.png', 'old');
    Storage::disk('public')->put('accounts/new.png', 'new');
    $account = Account::factory()->create(['logo' => 'accounts/old.png']);

    $account->update(['logo' => 'accounts/new.png']);

    Storage::disk('public')->assertMissing('accounts/old.png');
    Storage::disk('public')->assertExists('accounts/new.png');
});

it('keeps the file when it is unchanged on update', function (): void {
    Storage::disk('public')->put('accounts/logo.png', 'data');
    $account = Account::factory()->create(['logo' => 'accounts/logo.png']);

    $account->update(['name' => 'Renamed']);

    Storage::disk('public')->assertExists('accounts/logo.png');
});

it('does nothing when there was no previous file', function (): void {
    Storage::disk('public')->put('accounts/new.png', 'new');
    $account = Account::factory()->create(['logo' => null]);

    $account->update(['logo' => 'accounts/new.png']);

    Storage::disk('public')->assertExists('accounts/new.png');
});
