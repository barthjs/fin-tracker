<?php

declare(strict_types=1);

use App\Actions\GetOrCreateDefaultCategory;
use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

it('creates a Demo category when none exists', function (): void {
    $category = resolve(GetOrCreateDefaultCategory::class)();

    expect($category->name)->toBe('Demo')
        ->and($category->user_id)->toBe(auth()->id());
    assertDatabaseHas('categories', ['name' => 'Demo', 'user_id' => auth()->id()]);
});

it('returns the existing Demo category without creating a new one', function (): void {
    $existing = Category::factory()->create(['name' => 'Demo']);

    $category = resolve(GetOrCreateDefaultCategory::class)();

    expect($category->id)->toBe($existing->id)
        ->and(Category::query()->where('name', 'Demo')->count())->toBe(1);
});

it('creates the default category for the given user', function (): void {
    $other = User::factory()->create();

    $category = resolve(GetOrCreateDefaultCategory::class)($other);

    expect($category->user_id)->toBe($other->id)
        ->and($category->name)->toBe('Demo');
});
