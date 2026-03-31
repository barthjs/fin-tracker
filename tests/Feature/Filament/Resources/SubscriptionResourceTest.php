<?php

declare(strict_types=1);

use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use App\Models\Account;
use App\Models\Category;
use App\Models\Subscription;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    $subscriptions = Subscription::factory()->count(3)->create(['account_id' => $account->id, 'category_id' => $category->id]);

    livewire(ListSubscriptions::class)
        ->assertOk()
        ->assertCanSeeTableRecords($subscriptions);
});

it('can delete a subscription', function () {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    $subscription = Subscription::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);

    livewire(ViewSubscription::class, ['record' => $subscription->id])
        ->callAction('delete')
        ->assertHasNoActionErrors();

    $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
});
