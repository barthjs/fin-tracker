<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Filament\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\Categories\RelationManagers\SubscriptionsRelationManager;
use App\Filament\Resources\Categories\RelationManagers\TransactionsRelationManager;
use App\Models\Account;
use App\Models\Category;
use App\Models\Subscription;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function (): void {
    $categories = Category::factory()->count(3)->create();

    livewire(ListCategories::class)
        ->assertOk()
        ->assertCanSeeTableRecords($categories)
        ->assertActionExists('import')
        ->assertActionExists('export');
});

it('can filter categories by inactivity', function (): void {
    $activeCategory = Category::factory()->create(['is_active' => true]);
    $inactiveCategory = Category::factory()->create(['is_active' => false]);

    livewire(ListCategories::class)
        ->assertCanSeeTableRecords([$activeCategory])
        ->assertCanNotSeeTableRecords([$inactiveCategory])
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactiveCategory])
        ->assertCanNotSeeTableRecords([$activeCategory]);
});

it('can create a category', function (): void {
    $data = [
        'name' => fake()->word(),
        'group' => CategoryGroup::VarExpenses,
        'color' => fake()->hexColor(),
        'is_active' => true,
    ];

    livewire(ListCategories::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('categories', $data);
});

it('renders the view page', function (): void {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, [
        'record' => $category->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'group' => $category->group,
        ], 'infolist');
});

it('can edit a category', function (): void {
    $category = Category::factory()->create(['group' => CategoryGroup::FixExpenses]);

    $data = [
        'name' => fake()->word(),
        'group' => CategoryGroup::VarExpenses,
        'color' => fake()->hexColor(),
        'is_active' => false,
    ];

    livewire(ViewCategory::class, ['record' => $category->id])
        ->callAction('edit', $data)
        ->assertHasNoFormErrors();

    assertDatabaseHas('categories', array_merge(['id' => $category->id], $data));
});

it('can bulk edit the group of categories', function (): void {
    $categories = Category::factory()->count(2)->create(['group' => CategoryGroup::VarExpenses]);

    livewire(ListCategories::class)
        ->callTableBulkAction('group', $categories, ['group' => CategoryGroup::FixRevenues->value]);

    foreach ($categories as $category) {
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'group' => CategoryGroup::FixRevenues->value,
            'type' => TransactionType::Revenue->value,
        ]);
    }
});

it('can delete a category', function (): void {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, ['record' => $category->id])
        ->callAction('delete')
        ->assertHasNoActionErrors();

    assertModelMissing($category);
});

it('can load the transactions relation manager', function (): void {
    $category = Category::factory()->create();

    livewire(TransactionsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($category->transactions);
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

it('can search categories by their group label', function (): void {
    $expense = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    $revenue = Category::factory()->create(['group' => CategoryGroup::VarRevenues]);

    livewire(ListCategories::class)
        ->searchTable(CategoryGroup::VarExpenses->getLabel())
        ->assertCanSeeTableRecords([$expense])
        ->assertCanNotSeeTableRecords([$revenue]);
});

it('filters categories by type via tabs', function (): void {
    $expense = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    $revenue = Category::factory()->create(['group' => CategoryGroup::VarRevenues]);

    livewire(ListCategories::class)
        ->set('activeTab', TransactionType::Expense->value)
        ->assertCanSeeTableRecords([$expense])
        ->assertCanNotSeeTableRecords([$revenue])
        ->set('activeTab', TransactionType::Revenue->value)
        ->assertCanSeeTableRecords([$revenue])
        ->assertCanNotSeeTableRecords([$expense]);
});
