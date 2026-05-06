<?php

declare(strict_types=1);

use App\Enums\PeriodUnit;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use App\Models\Account;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    $subscriptions = Subscription::factory()->count(3)->create(['account_id' => $account->id, 'category_id' => $category->id]);

    livewire(ListSubscriptions::class)
        ->assertOk()
        ->assertCanSeeTableRecords($subscriptions)
        ->assertActionExists('import')
        ->assertActionExists('export');
});

it('scopes subscriptions and filters by account, category, period, auto-generate, due date and inactivity', function (): void {
    $accountA = Account::factory()->create();
    $accountB = Account::factory()->create();
    $categoryX = Category::factory()->create();
    $categoryY = Category::factory()->create();

    $monthly = Subscription::factory()->create([
        'account_id' => $accountA->id,
        'category_id' => $categoryX->id,
        'period_unit' => PeriodUnit::Month,
        'auto_generate_transaction' => true,
        'next_payment_date' => today()->addDays(5),
        'is_active' => true,
    ]);
    $yearly = Subscription::factory()->withoutAutoGenerateTransaction()->create([
        'account_id' => $accountB->id,
        'category_id' => $categoryY->id,
        'period_unit' => PeriodUnit::Year,
        'next_payment_date' => today()->addDays(20),
        'is_active' => true,
    ]);
    $inactive = Subscription::factory()->create([
        'account_id' => $accountA->id,
        'category_id' => $categoryX->id,
        'next_payment_date' => today()->addDays(10),
        'is_active' => false,
    ]);

    $other = User::factory()->create();
    $otherSubscription = Subscription::factory()->create([
        'account_id' => Account::factory()->create(['user_id' => $other->id])->id,
        'category_id' => Category::factory()->create(['user_id' => $other->id])->id,
        'next_payment_date' => today()->addDays(5),
    ]);

    livewire(ListSubscriptions::class)
        ->assertCanSeeTableRecords([$monthly, $yearly, $inactive])
        ->assertCanNotSeeTableRecords([$otherSubscription])
        ->filterTable('account_id', $accountB)
        ->assertCanSeeTableRecords([$yearly])
        ->assertCanNotSeeTableRecords([$monthly])
        ->resetTableFilters()
        ->filterTable('category_id', $categoryY)
        ->assertCanSeeTableRecords([$yearly])
        ->assertCanNotSeeTableRecords([$monthly])
        ->resetTableFilters()
        ->filterTable('period_unit', PeriodUnit::Month)
        ->assertCanSeeTableRecords([$monthly])
        ->assertCanNotSeeTableRecords([$yearly])
        ->resetTableFilters()
        ->filterTable('auto_generate_transaction', true)
        ->assertCanSeeTableRecords([$monthly])
        ->assertCanNotSeeTableRecords([$yearly])
        ->resetTableFilters()
        ->filterTable('upcoming_payments', ['due_until' => today()->addDays(7)->toDateString()])
        ->assertCanSeeTableRecords([$monthly])
        ->assertCanNotSeeTableRecords([$yearly])
        ->resetTableFilters()
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactive])
        ->assertCanNotSeeTableRecords([$monthly, $yearly]);
});

it('can create a subscription', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $data = [
        'name' => 'Netflix',
        'amount' => 9.99,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'period_unit' => PeriodUnit::Month->value,
        'period_frequency' => 1,
        'started_at' => today()->toDateString(),
        'next_payment_date' => today()->addMonth()->toDateString(),
        'auto_generate_transaction' => true,
        'color' => '#ff0000',
        'is_active' => true,
    ];

    livewire(ListSubscriptions::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('subscriptions', [
        'name' => 'Netflix',
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 9.99,
    ]);
});

it('can edit a subscription', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    $subscription = Subscription::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'name' => 'Old Name',
        'next_payment_date' => today()->addMonth(),
    ]);

    $data = [
        'name' => 'New Name',
        'amount' => $subscription->amount,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'period_unit' => $subscription->period_unit->value,
        'period_frequency' => $subscription->period_frequency,
        'started_at' => $subscription->started_at->toDateString(),
        'next_payment_date' => $subscription->next_payment_date->toDateString(),
        'auto_generate_transaction' => false,
        'color' => $subscription->color,
        'is_active' => true,
    ];

    livewire(ViewSubscription::class, ['record' => $subscription->id])
        ->callAction('edit', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'name' => 'New Name']);
});

it('can delete a subscription', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    $subscription = Subscription::factory()->create(['account_id' => $account->id, 'category_id' => $category->id]);

    livewire(ViewSubscription::class, ['record' => $subscription->id])
        ->callAction('delete')
        ->assertHasNoActionErrors();

    assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
});

it('can bulk delete subscriptions', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    $subscriptions = Subscription::factory()->count(3)->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'next_payment_date' => today()->addMonth(),
    ]);

    livewire(ListSubscriptions::class)
        ->callTableBulkAction('delete', $subscriptions);

    foreach ($subscriptions as $subscription) {
        $this->assertModelMissing($subscription);
    }
});
