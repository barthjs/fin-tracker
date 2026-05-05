<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Category;
use App\Models\Subscription;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

it('trims the name when creating', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $subscription = Subscription::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'name' => '  Netflix  ',
    ]);

    expect($subscription->name)->toBe('Netflix');
    assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'name' => 'Netflix']);
});

it('trims the name when updating', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $subscription = Subscription::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'name' => 'Netflix',
    ]);

    $subscription->update(['name' => '  Spotify  ']);

    expect($subscription->fresh()?->name)->toBe('Spotify');
});
