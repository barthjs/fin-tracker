<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ApiError;
use App\Http\Requests\StorePortfolioRequest;
use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class PortfolioController
{
    use ApiResponse;

    /**
     * List portfolios
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $portfolios = QueryBuilder::for(Portfolio::class)
            ->allowedFilters([
                'name',
                AllowedFilter::exact('market_value'),
                AllowedFilter::exact('currency'),
                'description',
                'color',
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts([
                'name',
                'market_value',
                'currency',
                'is_active',
            ])
            ->defaultSort('name')
            ->paginate()
            ->appends((array) $request->query());

        return PortfolioResource::collection($portfolios);
    }

    /**
     * Create a new portfolio
     */
    public function store(StorePortfolioRequest $request): PortfolioResource
    {
        $portfolio = Portfolio::create($request->validated());

        return new PortfolioResource($portfolio);
    }

    /**
     * Get portfolio details
     */
    public function show(Portfolio $portfolio): PortfolioResource
    {
        return new PortfolioResource($portfolio);
    }

    /**
     * Update a portfolio
     */
    public function update(StorePortfolioRequest $request, Portfolio $portfolio): PortfolioResource
    {
        $portfolio->update($request->validated());

        return new PortfolioResource($portfolio);
    }

    /**
     * Delete a portfolio
     */
    public function destroy(Request $request, Portfolio $portfolio): Response|JsonResponse
    {
        if ($request->user()->cannot('delete', $portfolio)) {
            return $this->errorResponse(ApiError::FORBIDDEN);
        }

        $portfolio->delete();

        return response()->noContent();
    }
}
