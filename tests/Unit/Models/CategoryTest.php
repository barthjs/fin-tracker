<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Models\Category;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

it('returns the owner of the category', function (): void {
    $category = Category::factory()->create();

    expect($category->user->id)->toBe(auth()->user()->id);
});

it('trims the name when creating', function (): void {
    $category = Category::factory()->create(['name' => '  Groceries  ']);

    expect($category->name)->toBe('Groceries');
    assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Groceries']);
});

it('trims the name when updating', function (): void {
    $category = Category::factory()->create(['name' => 'Groceries']);

    $category->update(['name' => '  Rent  ']);

    expect($category->fresh()?->name)->toBe('Rent');
});

it('assigns the authenticated user when no user is given', function (): void {
    $category = Category::factory()->create();

    expect($category->user_id)->toBe(auth()->id());
});

it('derives the transaction type from the group', function (CategoryGroup $group, TransactionType $type): void {
    $category = Category::factory()->create(['group' => $group]);

    expect($category->type)->toBe($type);
})->with([
    'fixed expenses' => [CategoryGroup::FixExpenses, TransactionType::Expense],
    'variable expenses' => [CategoryGroup::VarExpenses, TransactionType::Expense],
    'fixed revenues' => [CategoryGroup::FixRevenues, TransactionType::Revenue],
    'variable revenues' => [CategoryGroup::VarRevenues, TransactionType::Revenue],
    'transfers' => [CategoryGroup::Transfers, TransactionType::Transfer],
]);

it('updates the type when the group changes', function (): void {
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    expect($category->type)->toBe(TransactionType::Expense);

    $category->update(['group' => CategoryGroup::FixRevenues]);

    expect($category->fresh()?->type)->toBe(TransactionType::Revenue);
});
