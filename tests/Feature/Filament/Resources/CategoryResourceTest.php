<?php

declare(strict_types=1);

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

it('can load the transactions relation manager', function () {
    $category = Category::factory()->create();

    livewire(TransactionsRelationManager::class, [
        'ownerRecord' => $category,
        'pageClass' => ViewCategory::class,
    ])
        ->assertOk()
        ->assertCanSeeTableRecords($category->transactions);
});
