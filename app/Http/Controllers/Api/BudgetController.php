<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreBudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Services\BudgetService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class BudgetController
{
    use ApiResponse;

    /**
     * List budgets
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $budgets = QueryBuilder::for(Budget::class)
            ->allowedFilters([
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('amount'),
                AllowedFilter::exact('period_unit'),
                AllowedFilter::exact('warning_threshold'),
                AllowedFilter::exact('notify_on_exceed'),
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts([
                'category_id',
                'amount',
                'period_unit',
                'starts_at',
                'ends_at',
                'is_active',
            ])
            ->defaultSort('-starts_at')
            ->paginate()
            ->appends((array) $request->query());

        return BudgetResource::collection($budgets);
    }

    /**
     * Create a new budget
     */
    public function store(StoreBudgetRequest $request, BudgetService $service): BudgetResource
    {
        $budget = $service->create($request->validated());

        return new BudgetResource($budget);
    }

    /**
     * Get budget details
     */
    public function show(Budget $budget): BudgetResource
    {
        return new BudgetResource($budget);
    }

    /**
     * Update a budget
     */
    public function update(StoreBudgetRequest $request, Budget $budget, BudgetService $service): BudgetResource
    {
        $budget = $service->update($budget, $request->validated());

        return new BudgetResource($budget);
    }

    /**
     * Delete a budget
     */
    public function destroy(Budget $budget): Response
    {
        $budget->delete();

        return response()->noContent();
    }
}
