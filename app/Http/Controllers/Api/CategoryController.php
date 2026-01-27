<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ApiError;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class CategoryController
{
    use ApiResponse;

    /**
     * List categories
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = QueryBuilder::for(Category::class)
            ->allowedFilters([
                'name',
                AllowedFilter::exact('group'),
                AllowedFilter::exact('type'),
                'color',
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts([
                'name',
                'group',
                'type',
                'is_active',
            ])
            ->defaultSort('name')
            ->paginate()
            ->appends((array) $request->query());

        return CategoryResource::collection($categories);
    }

    /**
     * Create a new category
     */
    public function store(StoreCategoryRequest $request): CategoryResource
    {
        $category = Category::create($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Get category details
     */
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    /**
     * Update a category
     */
    public function update(StoreCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Delete a category
     */
    public function destroy(Request $request, Category $category): JsonResponse|Response
    {
        if ($request->user()->cannot('delete', $category)) {
            return $this->errorResponse(ApiError::FORBIDDEN);
        }

        $category->delete();

        return response()->noContent();
    }
}
