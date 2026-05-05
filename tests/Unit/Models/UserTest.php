<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\Subscription;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Filament\Facades\Filament;

it('builds the filament name from the first and last name', function (): void {
    $user = User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Doe'])->fresh();

    expect($user->getFilamentName())->toBe('Jane Doe');
});

it('falls back to the username when no name is set', function (): void {
    $user = User::factory()->create(['first_name' => null, 'last_name' => null, 'username' => 'janed'])->fresh();

    expect($user->getFilamentName())->toBe('janed');
});

it('grants panel access only to active users', function (): void {
    $panel = Filament::getDefaultPanel();

    expect(User::factory()->create(['is_active' => true])->canAccessPanel($panel))->toBeTrue()
        ->and(User::factory()->inactive()->create()->canAccessPanel($panel))->toBeFalse();
});

it('resolves the avatar url', function (): void {
    expect(User::factory()->create(['avatar' => null])->getFilamentAvatarUrl())->toBeNull()
        ->and(User::factory()->create(['avatar' => 'avatars/a.png'])->getFilamentAvatarUrl())->toContain('avatars/a.png');
});

it('persists and reads the preferred locale', function (): void {
    $user = User::factory()->create();

    $user->setLocale('de');

    expect($user->fresh()?->preferredLocale())->toBe('de');
});

it('stores and reads the app authentication secret', function (): void {
    $user = User::factory()->create();

    $user->saveAppAuthenticationSecret('SECRET123');

    expect($user->fresh()?->getAppAuthenticationSecret())->toBe('SECRET123')
        ->and($user->getAppAuthenticationHolderName())->toBeString()->not->toBeEmpty();
});

it('stores and reads the recovery codes', function (): void {
    $user = User::factory()->create();

    $user->saveAppAuthenticationRecoveryCodes(['code-1', 'code-2']);

    expect($user->fresh()?->getAppAuthenticationRecoveryCodes())->toBe(['code-1', 'code-2']);
});

it('exposes its domain relations', function (): void {
    asUser();
    $user = auth()->user();

    $account = Account::factory()->create();
    $category = Category::factory()->create();
    Portfolio::factory()->create();
    Security::factory()->create();
    Subscription::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);
    Trade::factory()->create(['account_id' => $account->id]);
    Transaction::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);

    expect($user->accounts()->count())->toBeGreaterThanOrEqual(1)
        ->and($user->categories()->count())->toBeGreaterThanOrEqual(1)
        ->and($user->portfolios()->count())->toBeGreaterThanOrEqual(1)
        ->and($user->securities()->count())->toBeGreaterThanOrEqual(1)
        ->and($user->providers()->count())->toBe(0)
        ->and($user->subscriptions()->count())->toBe(1)
        ->and($user->trades()->count())->toBe(1)
        ->and($user->transactions()->count())->toBe(1);
});
