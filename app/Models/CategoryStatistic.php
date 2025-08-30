<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\CategoryStatisticScope;
use Database\Factories\CategoryStatisticFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $category_id
 * @property-read string $year
 * @property-read float $jan
 * @property-read float $feb
 * @property-read float $mar
 * @property-read float $apr
 * @property-read float $may
 * @property-read float $jun
 * @property-read float $jul
 * @property-read float $aug
 * @property-read float $sep
 * @property-read float $oct
 * @property-read float $nov
 * @property-read float $dec
 * @property-read Category $category
 */
final class CategoryStatistic extends Model
{
    /** @use HasFactory<CategoryStatisticFactory> */
    use HasFactory, HasUlids;

    public const array MONTHS = [
        'jan', 'feb', 'mar', 'apr', 'may', 'jun',
        'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
    ];

    public $timestamps = false;

    protected $table = 'category_statistics';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, float>
     */
    protected $attributes = [
        'jan' => 0.0,
        'feb' => 0.0,
        'mar' => 0.0,
        'apr' => 0.0,
        'may' => 0.0,
        'jun' => 0.0,
        'jul' => 0.0,
        'aug' => 0.0,
        'sep' => 0.0,
        'oct' => 0.0,
        'nov' => 0.0,
        'dec' => 0.0,
    ];

    /**
     * Get the sum of all months.
     */
    public function yearlySum(): float
    {
        $total = 0.0;

        foreach (self::MONTHS as $month) {
            $total += $this->{$month} ?? 0.0;
        }

        return $total;
    }

    /**
     * Category of this statistic.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    protected static function booted(): void
    {
        self::addGlobalScope(new CategoryStatisticScope);
    }
}
