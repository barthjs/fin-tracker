<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class SubscriptionController
{
    use ApiResponse;

    /**
     * List subscriptions
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $subscriptions = QueryBuilder::for(Subscription::class)
            ->allowedFilters([
                AllowedFilter::exact('account_id'),
                AllowedFilter::exact('category_id'),
                'name',
                'description',
                AllowedFilter::exact('amount'),
                AllowedFilter::exact('period_unit'),
                AllowedFilter::exact('auto_generate_transaction'),
                AllowedFilter::exact('remind_before_payment'),
                'color',
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts([
                'account_id',
                'category_id',
                'name',
                'amount',
                'period_unit',
                'next_payment_date',
                'last_generated_at',
                'is_active',
            ])
            ->defaultSort('next_payment_date')
            ->paginate()
            ->appends((array) $request->query());

        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Create a new subscription
     */
    public function store(StoreSubscriptionRequest $request, SubscriptionService $service): SubscriptionResource
    {
        $subscription = $service->create($request->validated());

        return new SubscriptionResource($subscription);
    }

    /**
     * Get subscription details
     */
    public function show(Subscription $subscription): SubscriptionResource
    {
        return new SubscriptionResource($subscription);
    }

    /**
     * Update a subscription
     */
    public function update(StoreSubscriptionRequest $request, Subscription $subscription, SubscriptionService $service): SubscriptionResource
    {
        $subscription = $service->update($subscription, $request->validated());

        return new SubscriptionResource($subscription);
    }

    /**
     * Delete a subscription
     */
    public function destroy(Subscription $subscription): Response
    {
        $subscription->delete();

        return response()->noContent();
    }
}
