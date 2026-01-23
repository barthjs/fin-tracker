<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class TransactionController
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = QueryBuilder::for(Transaction::class)
            ->allowedFilters([
                AllowedFilter::callback('date_from', function (Builder $query, mixed $value): void {
                    /** @var Builder<Transaction> $query */
                    $query->where('date_time', '>=', $value);
                }),
                AllowedFilter::callback('date_until', function (Builder $query, mixed $value): void {
                    /** @var Builder<Transaction> $query */
                    $query->where('date_time', '<=', $value);
                }),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('amount'),
                'payee',
                AllowedFilter::exact('account_id'),
                AllowedFilter::exact('transfer_account_id'),
                AllowedFilter::exact('category_id'),
            ])
            ->allowedSorts([
                'date_time',
                'type',
                'amount',
                'payee',
                'account_id',
                'transfer_account_id',
                'category_id',
            ])
            ->defaultSort('-date_time')
            ->paginate()
            ->appends((array) $request->query());

        return TransactionResource::collection($transactions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransactionRequest $request, TransactionService $service): TransactionResource
    {
        $transaction = $service->create($request->validated());

        return new TransactionResource($transaction);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): TransactionResource
    {
        return new TransactionResource($transaction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTransactionRequest $request, Transaction $transaction, TransactionService $service): TransactionResource
    {
        $transaction = $service->update($transaction, $request->validated());

        return new TransactionResource($transaction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction, TransactionService $service): Response
    {
        $service->delete($transaction);

        return response()->noContent();
    }
}
