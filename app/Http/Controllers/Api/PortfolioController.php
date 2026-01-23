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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
     */
    public function store(StorePortfolioRequest $request): PortfolioResource
    {
        $portfolio = Portfolio::create($request->validated());

        return new PortfolioResource($portfolio);
    }

    /**
     * Display the specified resource.
     */
    public function show(Portfolio $portfolio): PortfolioResource
    {
        return new PortfolioResource($portfolio);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePortfolioRequest $request, Portfolio $portfolio): PortfolioResource
    {
        $portfolio->update($request->validated());

        return new PortfolioResource($portfolio);
    }

    /**
     * Remove the specified resource from storage.
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
