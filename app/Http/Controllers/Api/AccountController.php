<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ApiError;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class AccountController
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $accounts = QueryBuilder::for(Account::class)
            ->allowedFilters([
                'name',
                AllowedFilter::exact('balance'),
                AllowedFilter::exact('currency'),
                'description',
                'color',
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts([
                'name',
                'balance',
                'currency',
                'is_active',
            ])
            ->defaultSort('name')
            ->paginate()
            ->appends((array) $request->query());

        return AccountResource::collection($accounts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccountRequest $request): AccountResource
    {
        $account = Account::create($request->validated());

        return new AccountResource($account);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account): AccountResource
    {
        return new AccountResource($account);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAccountRequest $request, Account $account): AccountResource
    {
        $account->update($request->validated());

        return new AccountResource($account);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Account $account): JsonResponse|Response
    {
        if ($request->user()->cannot('delete', $account)) {
            return $this->errorResponse(ApiError::FORBIDDEN);
        }

        $account->delete();

        return response()->noContent();
    }
}
