<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\StatisticResource;
use App\Models\CategoryStatistic;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

final class StatisticController
{
    use ApiResponse;

    /**
     * List category statistics
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $statistics = QueryBuilder::for(CategoryStatistic::class)
            ->allowedFilters([
                AllowedFilter::exact('year'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('category.type'),
                AllowedFilter::exact('category.group'),
            ])
            ->allowedIncludes([
                'category',
            ])
            ->allowedSorts([
                'year',
                'jan',
                'feb',
                'mar',
                'apr',
                'may',
                'jun',
                'jul',
                'aug',
                'sep',
                'oct',
                'nov',
                'dec',
            ])
            ->defaultSort('-year', 'category_id')
            ->paginate()
            ->appends((array) $request->query());

        return StatisticResource::collection($statistics);
    }

    /**
     * Get category statistic details
     */
    public function show(CategoryStatistic $statistic): StatisticResource
    {
        return new StatisticResource($statistic->loadMissing('category'));
    }
}
