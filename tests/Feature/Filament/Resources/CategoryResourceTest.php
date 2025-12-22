<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\Categories\RelationManagers\TransactionsRelationManager;
use App\Models\Category;

use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function () {
    $categories = Category::factory()->count(3)->create();

    livewire(ListCategories::class)
        ->assertOk()
        ->assertCanSeeTableRecords($categories);
});

it('can filter categories by inactivity', function () {
    $activeCategory = Category::factory()->create(['is_active' => true]);
    $inactiveCategory = Category::factory()->create(['is_active' => false]);

    livewire(ListCategories::class)
        ->assertCanSeeTableRecords([$activeCategory])
        ->assertCanNotSeeTableRecords([$inactiveCategory])
        ->filterTable('inactive', true)
        ->assertCanSeeTableRecords([$inactiveCategory])
        ->assertCanNotSeeTableRecords([$activeCategory]);
});

it('can create a category', function () {
    $data = [
        'name' => fake()->word(),
        'group' => CategoryGroup::VarExpenses,
        'color' => fake()->hexColor(),
        'is_active' => true,
    ];

    livewire(ListCategories::class)
        ->callAction('create', $data)
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('categories', $data);
});

it('renders the view page', function () {
    $category = Category::factory()->create();

    livewire(ViewCategory::class, [
        'record' => $category->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'group' => $category->group,
        ], 'infolist');
});

it('can edit a category', function () {
    $category = Category::factory()->create(['group' => CategoryGroup::FixExpenses]);

    $data = [
        'name' => fake()->word(),
        'group' => CategoryGroup::VarExpenses,
        'color' => fake()->hexColor(),
        'is_active' => false,
    ];

    livewire(ViewCategory::class, ['record' => $category->id])
        ->callAction('edit', $data)
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('categories', array_merge(['id' => $category->id], $data));
});

it('can load the transactions relation manager', function () {
    $category = Category::factory()->create();

    livewire(TransactionsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($category->transactions);
});
