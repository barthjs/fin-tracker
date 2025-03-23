<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Category;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class CategoryChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '300px';

    protected static ?string $pollingInterval = null;

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => false,
            ],
        ],
        'scales' => [
            'y' => [
                'display' => false,
            ],
            'x' => [
                'display' => false,
            ],
        ],
    ];

    public function getHeading(): Htmlable|string|null
    {
        return __('category.navigation_label');
    }

    protected function getData(): array
    {
        $startDate = $this->filters['created_from'] ?? Carbon::today()->startOfYear();
        $endDate = $this->filters['created_until'] ?? null;
        $categories = Category::whereActive(true)->where('type', '!=', TransactionType::transfer)->get();
        $categoryLabels = [];
        $categoryData = [];
        $backgroundColors = [];
        foreach ($categories as $category) {
            $categoryLabels[] = $category->name;
            $categoryData[] = $category->transactions()
                ->when($startDate, fn (Builder $query) => $query->whereDate('date_time', '>=', $startDate))
                ->when($endDate, fn (Builder $query) => $query->whereDate('date_time', '<=', $endDate))
                ->sum('amount') / 100;
            $backgroundColors[] = $category->color;
        }

        return [
            'datasets' => [
                [
                    'data' => $categoryData,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $categoryLabels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
