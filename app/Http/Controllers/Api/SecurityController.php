<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ApiError;
use App\Http\Requests\StoreSecurityRequest;
use App\Http\Resources\SecurityResource;
use App\Models\Security;
use App\Services\SecurityService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class SecurityController
{
    use ApiResponse;

    /**
     * List securities
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $securities = QueryBuilder::for(Security::class)
            ->allowedFilters([
                'name',
                'isin',
                'symbol',
                AllowedFilter::exact('type'),
                AllowedFilter::exact('total_quantity'),
                AllowedFilter::exact('market_value'),
                'description',
                'color',
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts([
                'name',
                'isin',
                'symbol',
                'type',
                'price',
                'total_quantity',
                'market_value',
            ])
            ->defaultSort('name')
            ->paginate()
            ->appends((array) $request->query());

        return SecurityResource::collection($securities);
    }

    /**
     * Create a new security
     */
    public function store(StoreSecurityRequest $request): SecurityResource
    {
        $security = Security::create($request->validated());

        return new SecurityResource($security);
    }

    /**
     * Get security details
     */
    public function show(Security $security): SecurityResource
    {
        return new SecurityResource($security);
    }

    /**
     * Update a security
     */
    public function update(StoreSecurityRequest $request, SecurityService $service, Security $security): SecurityResource
    {
        $service->update($security, $request->validated());

        return new SecurityResource($security);
    }

    /**
     * Delete a security
     */
    public function destroy(Request $request, Security $security): Response|JsonResponse
    {
        if ($request->user()->cannot('delete', $security)) {
            return $this->errorResponse(ApiError::FORBIDDEN);
        }

        $security->delete();

        return response()->noContent();
    }
}
