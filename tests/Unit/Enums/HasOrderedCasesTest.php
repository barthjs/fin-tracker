<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Models\Category;

it('orders a query by declaration order with null values last', function (): void {
    $sql = CategoryGroup::orderQueryUsing(Category::query(), 'group', 'desc')->toBase()->orders[0]['sql'];

    expect($sql)->toContain('CASE WHEN "group" IS NULL THEN '.count(CategoryGroup::cases()))
        ->and($sql)->toContain("WHEN 'fix_expenses' THEN 0 WHEN 'var_expenses' THEN 1 WHEN 'fix_revenues' THEN 2")
        ->and($sql)->toEndWith('desc');
});
