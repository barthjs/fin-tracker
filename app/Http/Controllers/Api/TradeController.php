<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTradeRequest;
use App\Http\Resources\TradeResource;
use App\Models\Trade;
use App\Services\TradeService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class TradeController
{
    use ApiResponse;

    /**
     * List trades
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $trades = QueryBuilder::for(Trade::class)
            ->allowedFilters([
                AllowedFilter::callback('date_from', function (Builder $query, mixed $value): void {
                    /** @var Builder<Trade> $query */
                    $query->where('date_time', '>=', $value);
                }),
                AllowedFilter::callback('date_until', function (Builder $query, mixed $value): void {
                    /** @var Builder<Trade> $query */
                    $query->where('date_time', '<=', $value);
                }),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('total_amount'),
                AllowedFilter::exact('quantity'),
                AllowedFilter::exact('price'),
                AllowedFilter::exact('tax'),
                AllowedFilter::exact('fee'),
                'notes',
                AllowedFilter::exact('account_id'),
                AllowedFilter::exact('portfolio_id'),
                AllowedFilter::exact('security_id'),
            ])
            ->allowedSorts([
                'date_time',
                'type',
                'total_amount',
                'quantity',
                'price',
                'tax',
                'fee',
                'account_id',
                'portfolio_id',
                'security_id',
            ])
            ->defaultSort('-date_time')
            ->paginate()
            ->appends((array) $request->query());

        return TradeResource::collection($trades);
    }

    /**
     * Create a new trade
     */
    public function store(StoreTradeRequest $request, TradeService $service): TradeResource
    {
        $trade = $service->create($request->validated());

        return new TradeResource($trade);
    }

    /**
     * Get trade details
     */
    public function show(Trade $trade): TradeResource
    {
        return new TradeResource($trade);
    }

    /**
     * Update a trade
     */
    public function update(StoreTradeRequest $request, Trade $trade, TradeService $service): TradeResource
    {
        $trade = $service->update($trade, $request->validated());

        return new TradeResource($trade);
    }

    /**
     * Delete a trade
     */
    public function destroy(Trade $trade, TradeService $service): Response
    {
        $service->delete($trade);

        return response()->noContent();
    }
}
